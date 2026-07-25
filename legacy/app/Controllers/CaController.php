<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Helper;
use App\Core\Session;

class CaController extends Controller
{
    public function dashboard(): void
    {
        $caId = Auth::id();
        $stats = [
            'assigned' => Database::fetch('SELECT COUNT(*) as c FROM itr_filings WHERE ca_id = ?', [$caId])['c'] ?? 0,
            'review' => Database::fetch("SELECT COUNT(*) as c FROM itr_filings WHERE ca_id = ? AND status IN ('assigned','under_review','docs_requested')", [$caId])['c'] ?? 0,
            'filed' => Database::fetch("SELECT COUNT(*) as c FROM itr_filings WHERE ca_id = ? AND status IN ('filed','completed')", [$caId])['c'] ?? 0,
            'pending_docs' => Database::fetch("SELECT COUNT(*) as c FROM document_requests WHERE ca_id = ? AND status = 'open'", [$caId])['c'] ?? 0,
        ];

        $clients = Database::fetchAll(
            "SELECT f.*, u.name as client_name, u.email as client_email, u.phone as client_phone, p.name as plan_name
             FROM itr_filings f
             JOIN users u ON u.id = f.user_id
             LEFT JOIN plans p ON p.id = f.plan_id
             WHERE f.ca_id = ?
             ORDER BY f.updated_at DESC LIMIT 10",
            [$caId]
        );

        $this->view('ca/dashboard', [
            'title' => 'CA Dashboard',
            'stats' => $stats,
            'clients' => $clients,
        ], 'layouts/panel');
    }

    public function clients(): void
    {
        $status = $this->input('status');
        $sql = "SELECT f.*, u.name as client_name, u.email as client_email, u.phone as client_phone, p.name as plan_name
                FROM itr_filings f
                JOIN users u ON u.id = f.user_id
                LEFT JOIN plans p ON p.id = f.plan_id
                WHERE f.ca_id = ?";
        $params = [Auth::id()];

        if ($status) {
            $sql .= ' AND f.status = ?';
            $params[] = $status;
        }
        $sql .= ' ORDER BY f.updated_at DESC';

        $clients = Database::fetchAll($sql, $params);
        $this->view('ca/clients', [
            'title' => 'Assigned Clients',
            'clients' => $clients,
            'filter' => $status,
        ], 'layouts/panel');
    }

    public function showFiling(string $id): void
    {
        $filing = $this->caFiling((int) $id);
        $client = Database::fetch('SELECT * FROM users WHERE id = ?', [$filing['user_id']]);
        $docs = Database::fetchAll('SELECT * FROM documents WHERE filing_id = ? ORDER BY created_at DESC', [$id]);
        $notes = Database::fetchAll(
            'SELECT n.*, u.name as author_name FROM notes n JOIN users u ON u.id = n.author_id
             WHERE n.filing_id = ? ORDER BY n.created_at DESC',
            [$id]
        );
        $requests = Database::fetchAll('SELECT * FROM document_requests WHERE filing_id = ? ORDER BY created_at DESC', [$id]);
        $receipt = Database::fetch('SELECT * FROM receipts WHERE filing_id = ? ORDER BY id DESC LIMIT 1', [$id]);
        $logs = Database::fetchAll('SELECT * FROM status_logs WHERE filing_id = ? ORDER BY created_at ASC', [$id]);

        $this->view('ca/filing', [
            'title' => 'Client Filing #' . $id,
            'filing' => $filing,
            'client' => $client,
            'docs' => $docs,
            'notes' => $notes,
            'requests' => $requests,
            'receipt' => $receipt,
            'logs' => $logs,
        ], 'layouts/panel');
    }

    public function addNote(string $id): void
    {
        $this->validateCsrf();
        $this->caFiling((int) $id);

        $note = $this->input('note');
        if (!$note) {
            Session::flash('error', 'Note cannot be empty.');
            $this->redirect('/ca/filings/' . $id);
        }

        $isInternal = $this->input('is_internal') === '1' ? 1 : 0;
        Database::insert('notes', [
            'filing_id' => (int) $id,
            'author_id' => Auth::id(),
            'note' => $note,
            'is_internal' => $isInternal,
        ]);

        if (!$isInternal) {
            $filing = Database::fetch('SELECT user_id FROM itr_filings WHERE id = ?', [$id]);
            Helper::notify((int) $filing['user_id'], 'New note from CA', $note, '/track/' . $id);
        }

        Session::flash('success', 'Note added.');
        $this->redirect('/ca/filings/' . $id);
    }

    public function requestDocuments(string $id): void
    {
        $this->validateCsrf();
        $filing = $this->caFiling((int) $id);
        $message = $this->input('message');
        $required = $this->input('required_docs');

        if (!$message) {
            Session::flash('error', 'Please describe what documents are needed.');
            $this->redirect('/ca/filings/' . $id);
        }

        Database::insert('document_requests', [
            'filing_id' => (int) $id,
            'ca_id' => Auth::id(),
            'message' => $message,
            'required_docs' => $required,
            'status' => 'open',
        ]);

        Database::update('itr_filings', [
            'status' => 'docs_requested',
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);

        Helper::logStatus((int) $id, $filing['status'], 'docs_requested', Auth::id(), $message);
        Helper::notify((int) $filing['user_id'], 'More documents required', $message, '/documents/' . $id);

        Session::flash('success', 'Document request sent to client.');
        $this->redirect('/ca/filings/' . $id);
    }

    public function startReview(string $id): void
    {
        $this->validateCsrf();
        $filing = $this->caFiling((int) $id);

        Database::update('itr_filings', [
            'status' => 'under_review',
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);

        Helper::logStatus((int) $id, $filing['status'], 'under_review', Auth::id(), 'CA started review');
        Helper::notify((int) $filing['user_id'], 'Under Review', 'Your CA has started reviewing your documents.', '/track/' . $id);
        Session::flash('success', 'Marked as under review.');
        $this->redirect('/ca/filings/' . $id);
    }

    public function markFiled(string $id): void
    {
        $this->validateCsrf();
        $filing = $this->caFiling((int) $id);
        $ack = $this->input('acknowledgement_no');

        if (!$ack) {
            Session::flash('error', 'Acknowledgement number is required.');
            $this->redirect('/ca/filings/' . $id);
        }

        Database::update('itr_filings', [
            'status' => 'filed',
            'acknowledgement_no' => $ack,
            'filed_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);

        Helper::logStatus((int) $id, $filing['status'], 'filed', Auth::id(), 'ITR filed. ACK: ' . $ack);
        Helper::notify((int) $filing['user_id'], 'ITR Filed Successfully', 'Your return has been filed. ACK: ' . $ack, '/acknowledgement/' . $id);

        Session::flash('success', 'Filing marked as filed.');
        $this->redirect('/ca/filings/' . $id);
    }

    public function uploadReceipt(string $id): void
    {
        $this->validateCsrf();
        $filing = $this->caFiling((int) $id);

        if (empty($_FILES['receipt'])) {
            Session::flash('error', 'Please select the ITR-V / acknowledgement file.');
            $this->redirect('/ca/filings/' . $id);
        }

        $uploaded = Helper::upload($_FILES['receipt'], 'receipts');
        if (!$uploaded) {
            Session::flash('error', 'Upload failed.');
            $this->redirect('/ca/filings/' . $id);
        }

        $ack = $this->input('acknowledgement_no') ?: $filing['acknowledgement_no'];

        Database::insert('receipts', [
            'filing_id' => (int) $id,
            'uploaded_by' => Auth::id(),
            'acknowledgement_no' => $ack,
            'file_path' => $uploaded['file_path'],
            'original_name' => $uploaded['original_name'],
        ]);

        Database::update('itr_filings', [
            'status' => 'completed',
            'acknowledgement_no' => $ack,
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);

        Helper::logStatus((int) $id, $filing['status'], 'completed', Auth::id(), 'ITR receipt uploaded');
        Helper::notify((int) $filing['user_id'], 'Acknowledgement Ready', 'Download your ITR acknowledgement now.', '/acknowledgement/' . $id);

        Session::flash('success', 'Receipt uploaded. Filing completed.');
        $this->redirect('/ca/filings/' . $id);
    }

    public function downloadDoc(string $docId): void
    {
        $doc = Database::fetch('SELECT d.*, f.ca_id FROM documents d JOIN itr_filings f ON f.id = d.filing_id WHERE d.id = ?', [$docId]);
        if (!$doc || (int) $doc['ca_id'] !== Auth::id()) {
            Session::flash('error', 'Document not found.');
            $this->redirect('/ca/clients');
        }

        $path = __DIR__ . '/../../storage/' . $doc['file_path'];
        if (!file_exists($path)) {
            Session::flash('error', 'File missing.');
            $this->redirect('/ca/filings/' . $doc['filing_id']);
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($doc['original_name']) . '"');
        readfile($path);
        exit;
    }

    private function caFiling(int $id): array
    {
        $filing = Database::fetch('SELECT * FROM itr_filings WHERE id = ? AND ca_id = ?', [$id, Auth::id()]);
        if (!$filing) {
            Session::flash('error', 'Filing not assigned to you.');
            $this->redirect('/ca/clients');
        }
        return $filing;
    }
}

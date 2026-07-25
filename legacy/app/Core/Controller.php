<?php

namespace App\Core;

class Controller
{
    protected function view(string $view, array $data = [], ?string $layout = 'layouts/main'): void
    {
        View::render($view, $data, $layout);
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }

    protected function back(): void
    {
        $ref = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($ref);
    }

    protected function validateCsrf(): void
    {
        $token = $_POST['_token'] ?? '';
        if (!Session::verifyCsrf($token)) {
            Session::flash('error', 'Invalid security token. Please try again.');
            $this->back();
        }
    }

    protected function input(string $key, mixed $default = null): mixed
    {
        return trim((string) ($_POST[$key] ?? $_GET[$key] ?? $default));
    }

    protected function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}

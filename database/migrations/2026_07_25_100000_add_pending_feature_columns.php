<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable()->after('email');
            }
        });

        Schema::table('itr_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('itr_orders', 'everify_status')) {
                $table->string('everify_status')->default('pending')->after('acknowledgement_no');
            }
            if (! Schema::hasColumn('itr_orders', 'everified_at')) {
                $table->timestamp('everified_at')->nullable()->after('everify_status');
            }
            if (! Schema::hasColumn('itr_orders', 'ais_tds')) {
                $table->decimal('ais_tds', 12, 2)->nullable()->after('total_deductions');
            }
            if (! Schema::hasColumn('itr_orders', 'form16_tds')) {
                $table->decimal('form16_tds', 12, 2)->nullable()->after('ais_tds');
            }
        });

        if (Schema::hasTable('support_tickets')) {
            Schema::table('support_tickets', function (Blueprint $table) {
                if (! Schema::hasColumn('support_tickets', 'email')) {
                    $table->string('email')->nullable()->after('user_id');
                }
                if (! Schema::hasColumn('support_tickets', 'name')) {
                    $table->string('name')->nullable()->after('email');
                }
                if (! Schema::hasColumn('support_tickets', 'phone')) {
                    $table->string('phone')->nullable()->after('name');
                }
            });

            $driver = Schema::getConnection()->getDriverName();
            if ($driver === 'sqlite') {
                // SQLite tests: recreate not required; guest tickets use a system user fallback in app code.
            } elseif (in_array($driver, ['mysql', 'mariadb'], true)) {
                try {
                    DB::statement('ALTER TABLE support_tickets MODIFY user_id BIGINT UNSIGNED NULL');
                } catch (Throwable) {
                    // Column may already be nullable.
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'email_verified_at')) {
                $table->dropColumn('email_verified_at');
            }
        });

        Schema::table('itr_orders', function (Blueprint $table) {
            foreach (['everify_status', 'everified_at', 'ais_tds', 'form16_tds'] as $col) {
                if (Schema::hasColumn('itr_orders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

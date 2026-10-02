<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('database_backups', 'filename')) {
            Schema::table('database_backups', function (Blueprint $table) {
                $table->string('filename')->after('id');
            });
        }

        if (! Schema::hasColumn('database_backups', 'path')) {
            Schema::table('database_backups', function (Blueprint $table) {
                $table->string('path')->after('filename');
            });
        }

        if (! Schema::hasColumn('database_backups', 'type')) {
            Schema::table('database_backups', function (Blueprint $table) {
                $table->string('type')->default('manual')->after('path');
            });
        }

        if (! Schema::hasColumn('database_backups', 'size')) {
            Schema::table('database_backups', function (Blueprint $table) {
                $table->unsignedBigInteger('size')->nullable()->after('type');
            });
        }

        if (! Schema::hasColumn('database_backups', 'status')) {
            Schema::table('database_backups', function (Blueprint $table) {
                $table->string('status')->default('success')->after('size');
            });
        }

        if (! Schema::hasColumn('database_backups', 'created_by')) {
            Schema::table('database_backups', function (Blueprint $table) {
                $table->foreignId('created_by')
                    ->nullable()
                    ->after('status')
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('database_backups', 'description')) {
            Schema::table('database_backups', function (Blueprint $table) {
                $table->text('description')->nullable()->after('created_by');
            });
        }

        // Indexes are normally already present if these columns were
        // created by the original migration. No need to recreate them here.
    }

    public function down(): void
    {
        // Intentionally left empty because this migration is designed
        // to synchronize an existing database structure safely.
    }
};
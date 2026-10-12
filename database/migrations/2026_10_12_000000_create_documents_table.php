<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 255);
            $table->uuid('folder_id');
            $table->string('category_id', 64);
            $table->uuid('owner_id');
            $table->enum('status', ['active', 'trashed'])->default('active');
            $table->dateTime('deleted_at')->nullable();
            $table->dateTime('created_at')->useCurrent();

            $table->index(['folder_id', 'status'], 'ix_documents_folder_status');
            $table->index('category_id', 'ix_documents_category');
            $table->index('owner_id', 'ix_documents_owner');

            if (DB::getDriverName() === 'mariadb' || DB::getDriverName() === 'mysql') {
                $table->fullText('name', 'ft_documents_name');
            }

            $table->foreign('folder_id', 'fk_documents_folder')
                ->references('id')
                ->on('folders')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('category_id', 'fk_documents_category')
                ->references('id')
                ->on('categories')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('owner_id', 'fk_documents_owner')
                ->references('id')
                ->on('users')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });

        if (DB::getDriverName() === 'mariadb' || DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE documents ADD CONSTRAINT ck_documents_trash_consistency CHECK ((status = 'trashed' AND deleted_at IS NOT NULL) OR (status = 'active' AND deleted_at IS NULL))");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};

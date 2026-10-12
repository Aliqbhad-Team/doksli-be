<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('document_id');
            $table->unsignedSmallInteger('version_number');
            $table->string('file_url', 500)->comment('Kunci/path di object storage (S3 atau setara)');
            $table->char('checksum', 64)->comment('SHA-256 heksadesimal, untuk deteksi duplikat');
            $table->unsignedBigInteger('size_bytes');
            $table->string('note', 255)->nullable();
            $table->uuid('uploaded_by');
            $table->dateTime('uploaded_at')->useCurrent();

            $table->unique(['document_id', 'version_number'], 'uq_document_versions_doc_version');
            $table->index('uploaded_by', 'ix_document_versions_uploaded_by');
            $table->index('checksum', 'ix_document_versions_checksum');

            $table->foreign('document_id', 'fk_document_versions_document')
                ->references('id')
                ->on('documents')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('uploaded_by', 'fk_document_versions_uploaded_by')
                ->references('id')
                ->on('users')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_versions');
    }
};

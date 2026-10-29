<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id'); 
            $table->string('action', 100)->comment('mis. "Unggah dokumen", "Lihat dokumen"');
            $table->string('object_type', 50)->nullable()->comment('mis. "document", "folder"; NULL untuk aksi tanpa objek (mis. login)');
            $table->uuid('object_id')->nullable()->comment('Polimorfik, mengikuti object_type. Tidak diberi FK.');
            $table->string('ip_address', 45)->comment('Mendukung IPv4 dan IPv6');
            $table->dateTime('created_at')->useCurrent();

            // Foreign Key: RESTRICT on delete, CASCADE on update
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->index(['user_id', 'created_at'], 'ix_audit_logs_user_time');
            $table->index(['object_type', 'object_id'], 'ix_audit_logs_object');
            $table->index('created_at', 'ix_audit_logs_created_at');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};


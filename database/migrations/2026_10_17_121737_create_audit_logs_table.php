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
        $table->uuid('user_id')->nullable(); 
        $table->string('action');
        $table->string('object_type');
        $table->uuid('object_id')->nullable(); 
        $table->string('ip_address', 45)->nullable();
        $table->timestamp('created_at')->nullable();

        // Relasi ke tabel users
        $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
    });
}
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('folder_permissions', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->uuid('folder_id');
        $table->enum('subject_type', ['user', 'group']);
        $table->uuid('subject_id');
        $table->enum('level', ['view', 'download', 'edit', 'manage']);
        $table->uuid('granted_by');
        $table->dateTime('granted_at')->useCurrent();

        // Pembuatan Unique Key
        $table->unique(['folder_id', 'subject_type', 'subject_id'], 'uq_folder_permissions_subject');

        // Pembuatan Foreign Key & Constraints
        $table->foreign('folder_id')->references('id')->on('folders')
              ->cascadeOnDelete()->cascadeOnUpdate();
              
        $table->foreign('granted_by')->references('id')->on('users')
              ->restrictOnDelete()->cascadeOnUpdate();
    });
}
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_tags', function (Blueprint $table) {
            $table->uuid('document_id');
            $table->string('tag_id', 36);

            $table->primary(['document_id', 'tag_id']);

            $table->foreign('document_id')
                  ->references('id')->on('documents')
                  ->cascadeOnDelete();

            $table->foreign('tag_id')
                  ->references('id')->on('tags')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_tags');
    }
};

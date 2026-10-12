<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('categories', function (Blueprint $table) {
        // id (primary key: uuid) -> string (length 64)
        $table->string('id', 64)->primary();
        
        // name -> string (length 64)
        $table->string('name', 64);
        
        // Custom timestamp & user tracking
        $table->string('created_by', 32)->nullable();
        $table->timestamp('created_dt')->nullable();
        $table->string('updated_by', 32)->nullable();
        $table->timestamp('updated_dt')->nullable();
    });
}
};

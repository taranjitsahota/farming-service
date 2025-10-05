<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('issues', function (Blueprint $table) {
            $table->id();
               $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('issue_type_id');
            $table->text('message')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();

            $table->foreign('issue_type_id')->references('id')->on('issue_types')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issues');
    }
};

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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('profile_photo_path')->nullable();
            $table->string('profile_photo_url')->nullable();
            $table->string('country_code', 5)->nullable();
            $table->string('contact_number')->unique()->nullable();
            $table->string('password');
            $table->enum('role', ['superadmin', 'admin', 'user', 'driver'])->default('user');
            $table->unsignedBigInteger('substation_id')->nullable();
            $table->boolean('profile_completed')->default(false);
            $table->softDeletes();
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('substation_id')->references('id')->on('substations')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

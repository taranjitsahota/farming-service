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
        Schema::create('interested_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('tehsil_id')->nullable();
            $table->unsignedBigInteger('district_id')->nullable();
            $table->unsignedBigInteger('state_id')->nullable();
            $table->unsignedBigInteger('village_id')->nullable();
            $table->string('name');
            $table->string('email');
            $table->string('contact_number');
            $table->string('village_name');
            $table->string('pincode');
            $table->string('district');
            $table->string('area_of_land');
            $table->string('land_unit');
            $table->string('type');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('tehsil_id')->references('id')->on('tehsils')->onDelete('cascade');
            $table->foreign('district_id')->references('id')->on('districts')->onDelete('cascade');
            $table->foreign('state_id')->references('id')->on('states')->onDelete('cascade');
            $table->foreign('village_id')->references('id')->on('villages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interested_users');
    }
};

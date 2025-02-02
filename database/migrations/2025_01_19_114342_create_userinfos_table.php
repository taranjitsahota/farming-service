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
        Schema::create('userinfos', function (Blueprint $table) {
            $table->id();
        $table->unsignedBigInteger('user_id');  // Foreign key reference
        $table->string('first_name');
        $table->string('last_name');
        $table->string('fathers_name');
        $table->string('pincode');
        $table->string('village');
        $table->string('post_office');
        $table->string('police_station');
        $table->string('district');
        $table->string('total_servicable_land');
        $table->softDeletes();
        $table->timestamps();

        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('userinfos');
    }
};

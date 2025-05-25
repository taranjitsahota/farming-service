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
        Schema::table('interested_users', function (Blueprint $table) {
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('village_name')->nullable();
            $table->string('pincode')->nullable();
            $table->string('district')->nullable();
            $table->string('area_of_land')->nullable();
            $table->string('land_unit')->nullable();
            $table->string('type');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interested_users', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->dropColumn('email');
            $table->dropColumn('contact_number');
            $table->dropColumn('village_name');
            $table->dropColumn('pincode');
            $table->dropColumn('district');
            $table->dropColumn('area_of_land');
            $table->dropColumn('land_unit');
            $table->dropColumn('type');
        });
    }
};

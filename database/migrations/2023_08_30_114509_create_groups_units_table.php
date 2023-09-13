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
        Schema::create('groups_units', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('unit_id');
            $table->foreign('unit_id')->references('id')->on('units');
            $table->foreignId('group_id')->constrained(table: 'groups');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups_units');
    }
};

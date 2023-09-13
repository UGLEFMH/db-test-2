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
        Schema::create('membership_categories', function (Blueprint $table) {
            $table->id('id');
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->integer('ordering')->default(0);
            $table->boolean('status')->default(1);
            $table->foreignId('created_by')->nullable()->constrained(table: 'users');
            $table->timestamps();
        });

        Schema::disableForeignKeyConstraints();
        Schema::table('memberships', function (Blueprint $table) {
            $table->foreignId('membership_category_id')->nullable()->constrained(table: 'membership_categories');
        });
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('memberships', function (Blueprint $table) {
            $table->dropForeign(['membership_category_id']);
            $table->dropColumn(['membership_category_id']);
        });

        Schema::dropIfExists('membership_categories');
    }
};

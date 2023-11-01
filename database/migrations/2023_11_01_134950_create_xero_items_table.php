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
        Schema::create('xero_items', function (Blueprint $table) {
            //Id column
            $table->id();
            // item id column
            $table->string('item_id', 255)->nullable();
            //code column
            $table->string('code', 255)->nullable();
            //description type column
            $table->text('description')->nullable();
            //purchase_description column
            $table->text('purchase_description')->nullable();
            // name column
            $table->string('name', 255)->nullable();
            // is_tracked_as_inventory column
            $table->tinyInteger('is_tracked_as_inventory')->nullable();
            // is_sold column
            $table->tinyInteger('is_sold')->nullable();
            // is_purchased column
            $table->tinyInteger('is_purchased')->nullable();
            // Column for user id who created the items
            $table->integer('created_by')->default(0);
            // Column for user id who modified the items
            $table->integer('modified_by')->default(0);
            // items created time and modified time columns
            $table->timestamps();
            // for softdelete
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('xero_items');
    }
};

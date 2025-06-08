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
        Schema::disableForeignKeyConstraints();

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_file_id')->constrained();
            $table->string('customer_id');
            $table->string('customer_email');
            $table->string('customer_name');
            $table->string('reference_number');
            $table->dateTime('payment_date');
            $table->decimal('original_amount', 15, 2);
            $table->string('original_currency', 3);
            $table->decimal('usd_amount', 15, 2)->nullable();
            $table->enum('status', ["unprocessed","processed","failed"])->default('unprocessed');
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

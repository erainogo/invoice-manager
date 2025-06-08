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

        Schema::create('payment_files', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->string('path');
            $table->enum('status', ["uploaded","processing","processed","failed"]);
            $table->timestamp('uploaded_at');
            $table->timestamp('processed_at')->nullable();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_files');
    }
};

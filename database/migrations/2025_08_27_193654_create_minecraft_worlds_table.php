<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('minecraft_worlds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('seed')->nullable();
            $table->string('version')->nullable();
            $table->json('data_packs')->nullable();
            $table->text('s3_path')->nullable();

            $table->bigInteger('user_id')->index();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->uuid('server_id')->nullable()->index();
            $table->timestamps();

            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('minecraft_worlds');
    }
};

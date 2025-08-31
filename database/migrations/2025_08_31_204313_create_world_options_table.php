<?php

use App\Models\WorldOptions;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('world_options', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('version', WorldOptions::VERSIONS);
            $table->enum('type', WorldOptions::WORLD_TYPES);
            $table->integer('radius')->default(100);

            $table->uuid('minecraft_world_id');
            $table->foreign('minecraft_world_id')
                ->references('id')
                ->on('minecraft_worlds');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('world_options');
    }
};

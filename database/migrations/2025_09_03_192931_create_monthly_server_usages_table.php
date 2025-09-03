<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('monthly_server_usages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('server_id');
            $table->bigInteger('user_id');
            $table->timestamp('starting_at')
                ->default(
                    Carbon::parse('first day of this month 00:00:00')
                );
            $table->bigInteger('uptime_in_seconds')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_server_usages');
    }
};

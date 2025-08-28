<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('servers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('ip')->nullable();
            $table->integer('port')->nullable();
            $table->string('ec2_instance_id')->nullable();
            $table->string('region')->nullable();
            $table->string('instance_type')->nullable();
            $table->enum('status', ['starting', 'started', 'stopping', 'stopped', 'terminated', 'pending'])->default('pending');
            $table->json('tags')->nullable();
            $table->timestamps();

            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servers');
    }
};

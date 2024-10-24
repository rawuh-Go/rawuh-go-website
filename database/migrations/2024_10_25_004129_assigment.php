<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->foreignId('created_by')->constrained('users');
            $table->enum('jenis_project', ['tim', 'personal']);
            $table->text('deskripsi');
            $table->date('tanggal_deadline');
            $table->enum('status', ['pending', 'in_progress', 'done', 'rejected'])->default('pending');
            $table->text('feedback')->nullable();
            $table->timestamps();
        });

        Schema::create('assignment_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('laporan')->nullable();
            $table->string('file_laporan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_user');
        Schema::dropIfExists('assignments');
    }
};
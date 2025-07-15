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
        Schema::create('generuses', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('nik')->unique()->nullable();
            $table->string('place_of_birth')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['Laki-laki', 'Perempuan'])->nullable();
            $table->enum('blood_type', ['Tidak Tahu', 'A', 'B', 'AB', 'O'])->default('Tidak Tahu'); 
            $table->string('photo_path')->nullable(); // Kolom untuk Foto

            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            // $table->string('father_nik')->nullable();
            // $table->string('mother_nik')->nullable();

            $table->foreignId('kelompok_id')->nullable()->constrained()->onDelete('set null'); // Relasi ke kelompok
            $table->enum('jenjang', ['Bayi-Balita','Caberawit', 'Pra-remaja', 'Remaja', 'Usia Mandiri', 'Berkeluarga'])->default('Caberawit');
            $table->string('address')->nullable();
            // $table->enum('status', ['Belum menikah', 'Sudah menikah'])->default('Belum menikah');
            $table->string('education_status')->nullable();
            $table->text('notes')->nullable();

            // Kolom untuk melacak siapa yang menginput/mengedit data
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generuses');
    }
};

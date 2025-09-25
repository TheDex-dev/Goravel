<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEscortsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('escorts', function (Blueprint $table) {
            $table->id();
            $table->enum('kategori_pengantar', ['Polisi', 'Ambulans', 'Perorangan']);
            $table->string('nama_pengantar');
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan']);
            $table->string('nomor_hp', 20);
            $table->string('plat_nomor', 20);
            $table->string('nama_pasien');
            $table->string('foto_pengantar')->nullable();
            // Optional tracking fields for session integration
            $table->string('submission_id')->nullable();
            $table->string('submitted_from_ip')->nullable();
            $table->boolean('api_submission')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('escorts');
    }
}

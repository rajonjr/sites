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
        Schema::create('choreographies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category'); // Gospel, Contemporain, Traditionnel, Urbain
            $table->string('level'); // Débutant, Intermédiaire, Avancé, Tous niveaux
            $table->string('duration');
            $table->string('verse');
            $table->string('img');
            $table->text('desc');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('choreographies');
    }
};

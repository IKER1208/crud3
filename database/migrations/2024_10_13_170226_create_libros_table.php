<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLibrosTable extends Migration
{
    public function up()
    {
        Schema::create('libros', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->unsignedBigInteger('editorial_id');
            $table->timestamps();
            $table->SoftDeletes();

            $table->foreign('editorial_id')->references('id')->on('editoriales')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('libros');
    }
}


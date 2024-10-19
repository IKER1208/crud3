<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEditorialsTable extends Migration
{
    public function up()
    {
        Schema::create('editorials', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('pais');
            $table->timestamps();
            $table->SoftDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('editorials');
    }
}


<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmpleadosTable extends Migration
{
    public function up()
    {
        Schema::create('empleados', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->unsignedBigInteger('sucursal_id');
            $table->SoftDeletes();
            $table->timestamps();

            $table->foreign('sucursal_id')->references('id')->on('sucursals')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('empleados');
    }
}

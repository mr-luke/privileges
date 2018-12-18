<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePrivRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('priv_roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->unsignedTinyInteger('level')->default(0); // Visitor,Employee,Manager,Director
            $table->unsignedInteger('parent_id')->nullable();
            $table->text('childs')->nullable();
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('priv_roles')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('priv_roles');
    }
}

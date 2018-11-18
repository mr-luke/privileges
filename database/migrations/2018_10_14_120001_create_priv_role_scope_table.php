<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePrivRoleScopeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('priv_role_scope', function (Blueprint $table) {
            $table->unsignedInteger('role_id');
            $table->string('scope_id');
            $table->unsignedTinyInteger('level')->default(0);

            $table->foreign('role_id')->references('id')->on('priv_roles')->onDelete('CASCADE');
            $table->foreign('scope_id')->references('id')->on('priv_scopes')->onDelete('CASCADE');
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

        Schema::dropIfExists('priv_role_scope');
    }
}

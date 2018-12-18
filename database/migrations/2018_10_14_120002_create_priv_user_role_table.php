<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use Mrluke\Privileges\Facades\Manager;

class CreatePrivUserRoleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $config = Manager::getAuthorizableMigration();

        Schema::create('priv_auth_role', function (Blueprint $table) use ($config) {
            // Depend on Authorizable table structure
            // package provides support for tree types
            // of primaryKey: integer, string & uuid.
            //
            if ($config['type'] == 'integer') {
                $table->unsignedInteger('auth_id');

            } elseif ($config['type'] == 'string') {
                $table->string('auth_id');

            } elseif ($config['type'] == 'uuid') {
                $table->uuid('auth_id');
            }
            $table->unsignedInteger('role_id');

            $table->foreign('auth_id')
                  ->references($config['key'])->on($config['table'])
                  ->onDelete('CASCADE');

            $table->foreign('role_id')
                  ->references('id')->on('priv_roles')
                  ->onDelete('CASCADE');
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

        Schema::dropIfExists('priv_user_role');
    }
}

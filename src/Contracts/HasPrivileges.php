<?php

namespace Mrluke\Privileges\Contracts;


interface HasPrivileges
{
    /**
     * Returns users personal aditional privileges.
     *
     * @return array
     */
    public function getPermissions() : array;

    /**
     * Return related Role.
     *
     * @return Illuminate\Database\Eloquent\Model
     */
    public function role();
}

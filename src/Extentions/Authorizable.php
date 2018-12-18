<?php

namespace Mrluke\Privileges\Extentions;

use Mrluke\Privileges\Models\Permission;
use Mrluke\Privileges\Models\Role;

/**
 * Authorizable trait provides base functions that
 * turns Model into Authorizable.
 *
 * @author    Łukasz Sitnicki (mr-luke)
 * @link      http://github.com/mr-luke/privileges
 *
 * @category  Laravel
 * @package   mr-luke/privileges
 * @license   MIT
 */
trait Authorizable
{
    /**
     * Returns users personal aditional privileges.
     *
     */
    public function permissions()
    {
        return $this->morphMany(Permission::class, 'grantable');
    }

    /**
     * Return related Roles.
     *
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'priv_auth_role', 'auth_id', 'role_id');
    }
}

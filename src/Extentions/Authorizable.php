<?php

namespace Mrluke\Privileges\Extentions;

use Mrluke\Privileges\Facades\Manager;
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

    /**
     * Assign a role to given Authorizable.
     *
     * @param  mixed $role
     * @return void
     */
    public function assignRole($role): void
    {
        Manager::assignRole($this, $role);
    }

    /**
     * Return permission level based on personal's & role's permission.
     *
     * @param  string|array $scopes
     * @return int
     */
    public function considerPermission($scopes): int
    {
        return Manager::considerPermission($this, $scopes);
    }

    /**
     * Grant or update premission for a Permitable.
     *
     * @param  string $scope
     * @param  int    $level
     * @return void
     */
    public function grantPermission(string $scope, int $level): void
    {
        Manager::grantPermission($this, $scope, $level);
    }

    /**
     * Regain permission for a Permitable.
     *
     * @param  string $scope
     * @return void
     */
    public function regainPermission(string $scope): void
    {
        Manager::regainPermission($this, $scope);
    }

    /**
     * Remove a role from given Authorizable.
     *
     * @param  mixed $role
     * @return void
     */
    public function removeRole($role): void
    {
        Manager::removeRole($this, $role);
    }
}

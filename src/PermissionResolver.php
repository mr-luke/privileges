<?php

namespace Mrluke\Privileges;

/**
 * Class PermissionResolver
 * @package Mrluke\Privileges
 *
 * @author  Hubert Smusz <hubert.smusz@movecloser.pl>
 * @version 1.0.0
 */
class PermissionResolver
{

    /**
     * @param          $subject
     * @param string   $scope
     * @param int|null $level
     *
     * @return array
     */
    public static function getScopeRestrictions($subject, string $scope, int $level = null): array
    {
        $scopes = $subject->roles->flatMap(function ($role) use ($scope, $level) {
            if ($level) {
                $permissions = $role->permissions->where('level', $level);
            } else {
                $permissions = $role->permissions;
            }

            return preg_grep("/^${scope}:/", array_column($permissions->toArray(), 'scope'));
        })->toArray();

        return array_map(function ($scope) {
            return explode(':', $scope)[1];
        }, $scopes);
    }
}

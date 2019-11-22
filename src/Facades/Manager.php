<?php

namespace Mrluke\Privileges\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void assignRole(Authorizable $auth, $role)
 * @method static int considerPermission(Authorizable $auth, $scope)
 * @method static array considerRestriction(Authorizable $auth)
 * @method static mixed detectScope(string $model)
 * @method static string getAuthorizableModel()
 * @method static array getAuthorizableMigration()
 * @method static mixed getPermission(Permitable $subject, string $scope)
 * @method static void grantPermission(Permitable $subject, string $scope, int $level)
 * @method static bool hasPermission(Permitable $subject, string $scope)
 * @method static bool hasRole(Permitable $subject, $role)
 * @method static void regainPermission(Permitable $subject, string $scope)
 * @method static void removeRole(Authorizable $auth, $role)
 * @method static array getScopeRestrictions($subject, string $scope)
 */
class Manager extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'mrluke-privileges-manager';
    }
}

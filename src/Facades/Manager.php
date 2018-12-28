<?php

namespace Mrluke\Privileges\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method void assignRole(Authorizable $auth, $role)
 * @method int considerPermission(Authorizable $auth, string $scope)
 * @method array considerRestriction(Authorizable $auth)
 * @method mixed detectScope(string $model)
 * @method string getAuthorizableModel()
 * @method array getAuthorizableMigration()
 * @method mixed getPermission(Permitable $subject, string $scope)
 * @method void grantPermission(Permitable $subject, string $scope, int $level)
 * @method bool hasPermission(Permitable $subject, string $scope)
 * @method bool hasRole(Permitable $subject, $role)
 * @method void regainPermission(Permitable $subject, string $scope)
 * @method void removeRole(Authorizable $auth, $role)
 *
 * @property mixed $allowed_value
 * @property string $authKeyName
 * @property mixed $denied_value
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

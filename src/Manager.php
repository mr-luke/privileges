<?php

namespace Mrluke\Privileges;

use InvalidArgumentException;
use Mrluke\Configuration\Contracts\ArrayHost as Host;
use Mrluke\Configuration\Exceptions\ConfigurationException;
use Mrluke\Privileges\Contracts\Authorizable;
use Mrluke\Privileges\Contracts\Permission;
use Mrluke\Privileges\Contracts\Permitable;
use Mrluke\Privileges\Contracts\Role;

/**
 * Manager is a class that provides complex methods
 * to assign Authorizable to permissions and role.
 *
 * @author    Łukasz Sitnicki (mr-luke)
 * @link      http://github.com/mr-luke/privileges
 *
 * @category  Laravel
 * @package   mr-luke/privileges
 * @license   MIT
 * @version   1.0.0
 */
class Manager
{
    /**
     * Authorizable primary key name.
     *
     * @var string
     */
    protected $authKeyName;

    /**
     * Authorizable primary key type.
     *
     * @var string
     */
    protected $authKeyType;

    /**
     * Authorizable table name.
     *
     * @var string
     */
    protected $authTable;

    /**
     * Configuration instance.
     *
     * @var \Mrluke\Configuration\Contracts\ArrayHost
     */
    protected $config;

    public function __construct(Host $config)
    {
        $this->config = $config;
    }

    /**
     * Assign Authorizable to given role.
     *
     * @param  \Mrluke\Privileges\Contracts\Authorizable $auth
     * @param  mixed                                     $role
     * @return void
     */
    public function assignRole(Authorizable $auth, $role): void
    {
        if ($role instanceof Role) {
            $auth->roles()->syncWithoutDetaching([$role->id]);
        } elseif (is_integer($role)) {
            $auth->roles()->syncWithoutDetaching([$role]);
        } elseif (is_array($role)) {
            $auth->roles()->syncWithoutDetaching($role);
        }

        throw new InvalidArgumentException(
            sprintf('[role] parameter must be type of integer or instance of \Mrluke\Privileges\Contracts\Role. %s type given.', gettype($role))
        );
    }

    /**
     * Return permission level based on personal's & role's permission.
     *
     * @param  \Mrluke\Privileges\Contracts\Authorizable $auth
     * @param  string                                    $scope
     * @return int
     *
     * @throws \InvalidArgumentException
     */
    public function considerPermission(Authorizable $auth, string $scope): int
    {
        if ($personal = $this->getPermission($auth, $scope)) {
            // Personal permissions has priority
            // over role's ones.
            //
            return $personal->level;
        }

        $general = 0;
        $auth->load('roles.permissions');

        foreach ($auth->roles as $r) {
            // Let's check if there's a given scope defined
            // as a permission in any of Authorizable roles.
            //
            if ($p = $this->getPermission($r, $scope)) {
                ($p->level < $general) ?: $general = $p->level;
            }
        }

        return $general;
    }

    /**
     * Return restrictions based on roles.
     *
     * @param  \Mrluke\Privileges\Contracts\Authorizable $auth
     * @param  string                                    $scope
     * @return int
     *
     * @throws \InvalidArgumentException
     */
    public function considerRestriction(Authorizable $auth, string $scope): array
    {
        $restrictions = [];
        $level        = 0;

        $auth->load('roles.permissions');

        foreach ($auth->roles as $r) {
            // Let's check if there's a given scope in Parmitable's
            // ones and consider Role's level.
            //
            if ($this->hasPermission($r, $scope)) {
                ($level < $r->level) ?: $restrictions = $r->restrictions;
            }
        }

        return $restrictions;
    }

    /**
     * Detect which scope should be applied for given model.
     *
     * @param  string $model
     * @return string|null
     */
    public function detectScope(string $model)
    {
        return $this->config->get(
            'mapping',
            $this->config->get('mapping_default')
        );
    }

    /**
     * Return Authorizable model reference.
     *
     * @return string
     */
    public function getAuthorizableModel(): string
    {
        return $this->config->get('authorizable');
    }

    /**
     * Return Authorizable migration config.
     *
     * @return array
     */
    public function getAuthorizableMigration(): array
    {
        if (is_null($this->authKeyName)) {
            $instance = new $this->config->get('authorizable');

            if (!$instance instanceof Model) {
                throw new ConfigurationException(
                    sprintf('An instance of [authorizable] should be \Illuminate\Databe\Eloquent\Model. %s given.', get_class($instance))
                );
            }

            $this->authKeyName = $instance->getKeyName();
            $this->authKeyType = $instance->getKeyType();
            $this->authTable   = $instance->getTable();
        }

        return [
            'key'   => $this->authKeyName,
            'type'  => $this->authKeyType,
            'table' => $this->authTable
        ];
    }

    /**
     * Return Permission for given scope.
     *
     * @param  \Mrluke\Privileges\Contracts\Permitable $subject
     * @param  string                                  $scope
     * @return \Mrluke\Privileges\ContractsPermission|null
     *
     * @throws \InvalidArgumentException
     */
    public function getPermission(Permitable $subject, string $scope)
    {
        $this->checkScope($scope);

        return $subject->permissions->where('scope', $scope)->first();
    }

    /**
     * Grant or update premission for a Permitable.
     *
     * @param  \Mrluke\Privileges\Contracts\Permitable $subject
     * @param  string                                  $scope
     * @param  int                                     $level
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function grantPermission(Permitable $subject, string $scope, int $level): void
    {
        $this->checkScopeAndLevel($scope, $level);

        $permission = $subject->permissions()->ofScope($scope)->first();

        $permission ? $permission->update(['level' => $level]) : $subject->permissions()->create([
            'scope' => $scope,
            'level' => $level
        ]);
    }

    /**
     * Return if there's a given scope Permission for a Permitable.
     *
     * @param  \Mrluke\Privileges\Contracts\Permitable $subject
     * @param  string                                  $scope
     * @return bool
     *
     * @throws \InvalidArgumentException
     */
    public function hasPermission(Permitable $subject, string $scope): bool
    {
        $this->checkScope($scope);

        return $subject->permissions->where('scope', $scope)->exists();
    }

    /**
     * Regain permission for a Permitable.
     *
     * @param  \Mrluke\Privileges\Contracts\Permitable $subject
     * @param  string                                  $scope
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function regainPermission(Permitable $subject, string $scope): void
    {
        $this->checkScope($scope);

        $subject->permissions()->ofScope($scope)->delete();
    }

    /**
     * Remove Authorizable's role.
     *
     * @param  \Mrluke\Privileges\Contracts\Authorizable $auth
     * @param  mixed                                     $role
     * @return void
     */
    public function removeRole(Authorizable $auth, $role): void
    {
        if ($role instanceof Role) {
            $auth->roles()->detach($role->id);
        } elseif (is_integer($role)) {
            $auth->roles()->detach($role);
        }

        throw new InvalidArgumentException(
            sprintf('[role] parameter must be type of integer or instance of \Mrluke\Privileges\Contracts\Role. %s type given.', gettype($role))
        );
    }

    /**
     * Return class attributes or setting.
     *
     * @param  string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        if (in_array($name, ['authKeyName', 'authKeyType', 'authTable'])) {
            return $this->{$name};
        }

        return $this->config->get($name, null);
    }

    /**
     * Check if the scope value.
     *
     * @param  string $scope
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    private function checkScope(string $scope): void
    {
        if (!in_array($scope, $this->config->get('scopes'))) {
            throw new InvalidArgumentException('Given [scope] is not allowed.');
        }
    }

    /**
     * Check if the scope & level values.
     *
     * @param  string $scope
     * @param  int    $level
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    private function checkScopeAndLevel(string $scope, int $level): void
    {
        $this->checkScope($scope);

        if ($level >= 0 && $level < 5) {
            throw new InvalidArgumentException('Given [level] must be in range of 0-4.');
        }
    }
}

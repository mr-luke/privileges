<?php

namespace Mrluke\Privileges\Models;

use Illuminate\Database\Eloquent\Model;

use Mrluke\Privileges\Contracts\Role as Contract;
use Mrluke\Privileges\Facades\Manager;

/**
 * Role model for package.
 *
 * @author    Łukasz Sitnicki (mr-luke)
 * @link      http://github.com/mr-luke/privileges
 *
 * @category  Laravel
 * @package   mr-luke/privileges
 * @license   MIT
 */
class Role extends Model implements Contract
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'childs' => 'array',
        'level'  => 'integer',
    ];

    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'created'  => \Mrluke\Privileges\Events\Role\Created::class,
        'creating' => \Mrluke\Privileges\Events\Role\Creating::class,
        'deleted'  => \Mrluke\Privileges\Events\Role\Deleted::class,
        'deleting' => \Mrluke\Privileges\Events\Role\Deleting::class,
        'updated'  => \Mrluke\Privileges\Events\Role\Updated::class,
        'updating' => \Mrluke\Privileges\Events\Role\Updating::class,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'level', 'parent_id', 'childs'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'priv_roles';

    /**
     * Return assigned Authorizable models.
     *
     */
    public function assigned()
    {
        $model = Manager::getAuthorizableModel();

        return $this->belongsToMany($model, 'priv_auth_role', 'role_id', 'auth_id');
    }

    /**
     * Return related model.
     */
    public function parent()
    {
        return $this->belongsTo(Role::class, 'parent_id', 'id');
    }

    /**
     * Return related model.
     */
    public function childen()
    {
        return $this->hasMany(Role::class, 'parent_id', 'id');
    }

    /**
     * Return related model.
     */
    public function permissions()
    {
        return $this->morphMany(Permission::class, 'grantable');
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
}

<?php

namespace Mrluke\Privileges\Models;

use Illuminate\Database\Eloquent\Model;

use Mrluke\Privileges\Contracts\Permission as Contract;

/**
 * Permission model for package.
 *
 * @author    Łukasz Sitnicki (mr-luke)
 * @link      http://github.com/mr-luke/privileges
 *
 * @category  Laravel
 * @package   mr-luke/privileges
 * @license   MIT
 */
class Permission extends Model implements Contract
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'level'  => 'integer',
    ];

    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'created'  => \Mrluke\Privileges\Events\Permission\Created::class,
        'creating' => \Mrluke\Privileges\Events\Permission\Creating::class,
        'deleted'  => \Mrluke\Privileges\Events\Permission\Deleted::class,
        'deleting' => \Mrluke\Privileges\Events\Permission\Deleting::class,
        'updated'  => \Mrluke\Privileges\Events\Permission\Updated::class,
        'updating' => \Mrluke\Privileges\Events\Permission\Updating::class,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['scope', 'level'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'priv_permissions';

    /**
     * Return related model.
     */
    public function grantable()
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to only include specific scope.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  string                                $scope
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfScope($query, string $scope)
    {
        return $query->where('scope', $scope);
    }

    /**
     * Return permission level.
     *
     * @return int
     */
    public function getLevel():int
    {
        return $this->level;
    }
}

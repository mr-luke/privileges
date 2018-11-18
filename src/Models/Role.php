<?php

namespace Mrluke\Privileges\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
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
    public function scopes()
    {
        return $this->belongsToMany(Scope::class, 'priv_role_scope', 'scope_id', 'role_id')
                    ->withPivot(['level']);
    }
}

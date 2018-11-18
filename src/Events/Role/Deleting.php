<?php

namespace Mrluke\Privileges\Events\Role;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

use Mrluke\Privileges\Models\Role;

class Deleting
{
    use Dispatchable, SerializesModels;

    /**
     * Authenticable that cause Event.
     *
     * @var \Illuminate\Foundation\Auth\User
     */
    public $auth;

    /**
     * Instance of new model.
     *
     * @var \Mrluke\Privileges\Models\Role
     */
    public $model;

    public function __construct(Role $model)
    {
        $this->auth = auth()->user();
        $this->model = $model;
    }
}

<?php

namespace Mrluke\Privileges\Events\Permission;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

use Mrluke\Privileges\Models\Permission;

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
     * @var \Mrluke\Privileges\Models\Permission
     */
    public $model;

    public function __construct(Permission $model)
    {
        $this->auth = auth()->user();
        $this->model = $model;
    }
}

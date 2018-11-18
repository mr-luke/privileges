<?php

namespace Mrluke\Privileges\Events\Scope;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

use Mrluke\Privileges\Models\Scope;

class Updating
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
     * @var \Mrluke\Privileges\Models\Scope
     */
    public $model;

    public function __construct(Scope $model)
    {
        $this->auth = auth()->user();
        $this->model = $model;
    }
}

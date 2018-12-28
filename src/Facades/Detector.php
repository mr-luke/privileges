<?php

namespace Mrluke\Privileges\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method mixed has(Model $model, string $relation = null)
 * @method mixed hasOrLevel(Model $model, int $min, string $relation = null)
 * @method mixed level(int $min)
 * @method mixed owner(Model $model, string $foreign = null)
 * @method mixed ownerOrLevel(Model $model, int $min, string $foreign = null)
 * @method self scope(string $scope)
 * @method mixed share(Model $model, string $modelRelation, string $relation)
 * @method self subject(Authorizable $auth)
 */
class Detector extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'mrluke-privileges-detector';
    }
}

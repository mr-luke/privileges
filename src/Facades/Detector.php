<?php

namespace Mrluke\Privileges\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed has(Model $model, string $relation = null)
 * @method static mixed hasOrLevel(Model $model, int $min, string $relation = null)
 * @method static mixed level(int $min)
 * @method static mixed owner(Model $model, string $foreign = null)
 * @method static mixed ownerOrLevel(Model $model, int $min, string $foreign = null)
 * @method static self scope($scope)
 * @method static mixed share(Model $model, string $modelRelation, string $relation)
 * @method static self subject(Authorizable $auth)
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

<?php

namespace Mrluke\Privileges;

use Exception;
use InvalidArgumentException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

use Mrluke\Privileges\Contracts\Authorizable;
use Mrluke\Privileges\Manager;

/**
 * Detector is a class that provides full complex
 * method to determine Users privilege by its Role & external
 * conditions like: IP, Hours, Region etc.
 *
 * @author    Łukasz Sitnicki (mr-luke)
 * @link      http://github.com/mr-luke/privileges
 *
 * @category  Laravel
 * @package   mr-luke/privileges
 * @license   MIT
 * @version   1.0.0
 */
class Detector
{
    /**
     * Value return in case of allowed access.
     *
     * @var mixed
     */
    protected $allowed;

    /**
     * Value return in case of denid access.
     *
     * @var mixed
     */
    protected $denied;

    /**
     * Authorizable primary key name.
     *
     * @var string
     */
    protected $identifier;

    /**
     * Instance of privileges Manager.
     *
     * @var \Mrluke\Privileges\Manager
     */
    protected $manager;

    /**
     * Scope name.
     *
     * @var string|array
     */
    protected $scope;

    /**
     * Instance of Authorizable.
     *
     * @var \Mrluke\Privileges\Contracts\Authorizable
     */
    protected $subject;

    public function __construct(Manager $manager)
    {
        $this->manager    = $manager;

        $this->allowed    = $manager->allowed_value;
        $this->denied     = $manager->denied_value;
        $this->identifier = $manager->authKeyName;
    }

    /**
     * Determine if give Subject has resource.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $relation
     * @return mixed
     */
    public function has(Model $model, string $relation = null)
    {
        $this->hasSubjectSet();

        // First we need to check restritions for given Role
        // to detect special Location, IP, Hours conditions.
        if (! $this->checkRestrictions()) return $this->denied;

        return $this->hasModel($model, $relation) ? $this->allowed: $this->denied;
    }

    /**
     * Determines if Subject has resource or has enough privilege.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  int $min
     * @param  string $relation
     * @return mixed
     */
    public function hasOrLevel(Model $model, int $min, string $relation = null)
    {
        $this->hasSubjectAndScopeSet();

        // First we need to check restritions for given Role
        // to detect special Location, IP, Hours conditions.
        if (! $this->checkRestrictions()) return $this->denied;

        if ($this->hasModel($model, $relation)) return $this->allowed;

        return $this->hasLevel($min) ? $this->allowed : $this->denied;
    }

    /**
     * Determines if Subject has access to resource.
     *
     * @param  int     $min
     * @param  boolean $this->denied
     * @return mixed
     */
    public function level(int $min)
    {
        $this->hasSubjectAndScopeSet();

        // First we need to check restritions for given Role
        // to detect special Location, IP, Hours conditions.
        if (! $this->checkRestrictions()) return $this->denied;

        return $this->hasLevel($min) ? $this->allowed : $this->denied;
    }

    /**
     * Determines if Subject is owner of model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  boolean $this->denied
     * @param  string $foreign
     * @return mixed
     */
    public function owner(Model $model, string $foreign = null)
    {
        $this->hasSubjectSet();

        // First we need to check restritions for given Role
        // to detect special Location, IP, Hours conditions.
        if (! $this->checkRestrictions()) return $this->denied;

        return $this->isOwner($model, $foreign) ? $this->allowed : $this->denied;
    }

    /**
     * Determines if Subject is owner of model or has enough privilege.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  int $min
     * @param  boolean $this->denied
     * @param  string $foreign
     * @return mixed
     */
    public function ownerOrLevel(Model $model, int $min, string $foreign = null)
    {
        $this->hasSubjectAndScopeSet();

        // First we need to check restritions for given Role
        // to detect special Location, IP, Hours conditions.
        if (! $this->checkRestrictions()) return $this->denied;

        if ($this->isOwner($model, $foreign)) return $this->allowed;

        return $this->hasLevel($min) ? $this->allowed : $this->denied;
    }

    /**
     * Set scope that is checking.
     *
     * @param  string|array $scope
     * @return self
     */
    public function scope($scope): self
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * Determines if Subject and model shares instance.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string $modelRelation
     * @param  string $relation
     * @param  boolean $this->denied
     * @return mixed
     */
    public function share(Model $model, string $modelRelation, string $relation)
    {
        $this->hasSubjectSet();

        // First we need to check restritions for given Role
        // to detect special Location, IP, Hours conditions.
        if (! $this->checkRestrictions()) return $this->denied;

        return $this->isSharing($model, $modelRelation, $relation) ?
            $this->allowed : $this->denied;
    }

    /**
     * Set user that needs to be checked.
     *
     * @param  \Mrluke\Privileges\Contracts\Authorizable $user
     * @return self
     */
    public function subject(Authorizable $auth): self
    {
        $this->subject = $auth;

        return $this;
    }

    /**
     * Check if there is any restrition for subject's role.
     *
     * @return bool
     */
    protected function checkRestrictions(): bool
    {
        $result = true;
        // Let's get restritions and check
        // if its present.
        if ($restrictions = $this->manager->considerRestriction($this->subject)) {
            // We need to check if subjects's IP address is allowed
            // by it's Role to perform the action.
            if (isset($restrictions['ip']))
            {
                $result = $this->concernIpRestriction($restrictions['ip'] ?? []);
            }

            // We need to check if access hour is correct.
            if (isset($restrictions['hours']) && $result)
            {
                $result = $this->concernTimeRestriction($restrictions['time'] ?? []);
            }
        }

        return $result;
    }

    /**
     * Checks if given level is enough.
     *
     * @param  int  $min
     * @return bool
     */
    protected function hasLevel(int $min): bool
    {
        $level = $this->manager->considerPermission($this->subject, $this->scope);

        return $level >= $min;
    }

    /**
     * Determine if give Subject has resource.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string|null  $relation
     * @return bool
     */
    protected function hasModel(Model $model, $relation): bool
    {
        if (is_null($relation)) {
            // We need to detect foreign key of relation
            // to check if subject is an owner.
            $relation = Str::camel(Str::plural(class_basename($model)));
        }
        $foreign = $this->subject->$relation()->getRelatedPivotKeyName();

        return $this->subject->$relation()->where($foreign, $model->{$this->identifier})->exists();
    }

    /**
     * Check if Subject is owner of model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string|null $foreign
     * @return bool
     */
    protected function isOwner(Model $model, $foreign): bool
    {
        if (is_null($foreign)) {
            // We need to detect foreign key of relation
            // to check if subject is an owner.
            $class = Str::snake(class_basename($this->subject));

            $foreign = $model->$class()->getForeignKeyName();
        }

        return $this->subject->{$this->identifier} == $model->{$foreign};
    }

    /**
     * Chech if Subject and model shares instance.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string $modelRelation
     * @param  string $relation
     * @return bool
     */
    protected function isSharing(Model $model, $modelRelation, $relation): bool
    {
        $foreign = $model->$modelRelation()->getForeignKeyName();

        return $this->subject->$relation()->where($foreign, $model->id)->exists();
    }

    /**
     * Check if detector is correctly set.
     *
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function hasSubjectSet(): void
    {
        if (empty($this->subject)) {
            throw new InvalidArgumentException(
                'Setting subject is required. Use method subject() befor detection.'
            );
        }
    }

    /**
     * Check if detector is correctly set.
     *
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function hasSubjectAndScopeSet(): void
    {
        $this->hasSubjectSet();

        if (empty($this->scope)) {
            throw new InvalidArgumentException(
                'Setting scope is required. Use method scope() befor detection.'
            );
        }
    }

    /**
     * Compare IPs from list to given one.
     *
     * @param  array $rules
     * @param  float $long
     * @return bool
     */
    private function compareIPs(array $rules, float $long): bool
    {
        foreach ($rules as $ip) {
            $result = ($long == ip2long($ip)) ? true : false;
        }

        return $result ?? true;
    }

    /**
     * Check if given IP restrictions allows Authorizable to perform action.
     *
     * @param  array $restrictions
     * @return bool
     */
    private function concernIpRestriction(array $restrictions): bool
    {
        $ip     = ip2long(request()->ip());
        $rule   = $restrictions['rule'];
        $result = true;

        switch ($restrictions['type']) {
            case 'one':
                // Let's check if subject's IP is the same
                // as set one for Role.
                $result = $this->compareIPs($rule, $ip);
                break;

            case 'range':
                // Let's check if subject's IP is within
                // range set to Role.
                ($ip >= ip2long($rule[0]) && $ip <= ip2long($rule[1])) ?: $result = false;
                break;
        }

        return $result;
    }

    /**
     * Check if given Time restrictions allows Authorizable to perform action.
     *
     * @param  array $restrictions
     * @return bool
     */
    private function concernTimeRestriction(array $restrictions): bool
    {
        $now = now();
        $rule = $restrictions['rule'];

        switch ($restrictions['type']) {
            case 'day':
                return (in_array($now->dayOfWeekIso, $rule)) ? true : false;

            case 'hour':
                return ($now->hour >= $rule[0] && $now->hour <= $rule[1]) ? true : false;
        }

        return true;
    }
}

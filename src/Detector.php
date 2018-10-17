<?php

namespace Mrluke\Privileges;

use Exception;
use InvalidArgumentException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

use Mrluke\Privileges\Contracts\HasPrivileges;

/**
 * PrivilegesDetector is a class that provides full complex
 * method to determine Users privilege by its Role & external
 * conditions like: IP, Hours, Region etc.
 *
 * @author  https://github.com/mr-luke
 * @version 1.0.0
 * @license MIT
 */
class Detector
{
    /**
     * HasPrivileges identifier name.
     *
     * @var string
     */
    protected $identifier = 'id';

    /**
     * Privilege's name.
     *
     * @var string
     */
    protected $privilege;

    /**
     * Instance of HasPrivileges.
     *
     * @var HasPrivileges
     */
    protected $subject;

    /**
     * Determine if give Subject has resource.
     *
     * @param  Illuminate\Database\Eloquent\Model  $model
     * @param  boolean $deny
     * @param  string  $relation
     * @return bool
     */
    public function has(Model $model, bool $deny = false, string $relation = null) : bool
    {
        $this->hasSubjectSet();

        // First we need to check restritions for given Role
        // to detect special Location, IP, Hours conditions.
        if (! $this->checkRestrictions()) return $deny;

        return $this->hasModel($model, $relation) ? !$deny : $deny;
    }

    /**
     * Determines if Subject has resource or has enough privilege.
     *
     * @param  Illuminate\Database\Eloquent\Model  $model
     * @param  int $min
     * @param  boolean $deny
     * @param  string $relation
     * @return bool
     */
    public function hasOrLevel(Model $model, int $min, bool $deny = false, string $relation = null) : bool
    {
        $this->hasSubjectAndPrivilegeSet();

        // First we need to check restritions for given Role
        // to detect special Location, IP, Hours conditions.
        if (! $this->checkRestrictions()) return $deny;

        if ($this->hasModel($model, $relation)) return !$deny;

        return $this->hasLevel($min) ? !$deny : $deny;
    }

    /**
     * Determines if Subject has access to resource.
     *
     * @param  int     $min
     * @param  boolean $deny
     * @return bool
     */
    public function level(int $min, bool $deny = false) : bool
    {
        $this->hasSubjectAndPrivilegeSet();

        // First we need to check restritions for given Role
        // to detect special Location, IP, Hours conditions.
        if (! $this->checkRestrictions()) return $deny;

        return $this->hasLevel($min) ? !$deny : $deny;
    }

    /**
     * Determines if Subject is owner of model.
     *
     * @param  Illuminate\Database\Eloquent\Model  $model
     * @param  boolean $deny
     * @param  string $foreign
     * @return bool
     */
    public function owner(Model $model, bool $deny = false, string $foreign = null) : bool
    {
        $this->hasSubjectSet();

        // First we need to check restritions for given Role
        // to detect special Location, IP, Hours conditions.
        if (! $this->checkRestrictions()) return $deny;

        return $this->isOwner($model, $foreign) ? !$deny : $deny;
    }

    /**
     * Determines if Subject is owner of model or has enough privilege.
     *
     * @param  Illuminate\Database\Eloquent\Model  $model
     * @param  int $min
     * @param  boolean $deny
     * @param  string $foreign
     * @return bool
     */
    public function ownerOrLevel(Model $model, int $min, bool $deny = false, string $foreign = null) : bool
    {
        $this->hasSubjectAndPrivilegeSet();

        // First we need to check restritions for given Role
        // to detect special Location, IP, Hours conditions.
        if (! $this->checkRestrictions()) return $deny;

        if ($this->isOwner($model, $foreign)) return !$deny;

        return $this->hasLevel($min) ? !$deny : $deny;
    }

    /**
     * Set model that is restricted.
     *
     * @param  mixed $privilege
     * @return self
     */
    public function privilege(string $privilege) : self
    {
        $this->privilege = $privilege;

        return $this;
    }

    /**
     * Determines if Subject and model shares instance.
     *
     * @param  Illuminate\Database\Eloquent\Model  $model
     * @param  string $modelRelation
     * @param  string $relation
     * @param  boolean $deny
     * @return bool
     */
    public function share(Model $model, string $modelRelation, string $relation, bool $deny = false) : bool
    {
        $this->hasSubjectSet();

        // First we need to check restritions for given Role
        // to detect special Location, IP, Hours conditions.
        if (! $this->checkRestrictions()) return $deny;

        return $this->isSharing($model, $modelRelation, $relation) ? !$deny : $deny;
    }

    /**
     * Set user that needs to be checked.
     *
     * @param  HasPrivileges $user
     * @return self
     */
    public function subject(HasPrivileges $user) : self
    {
        $this->identifier = $user->getKeyName();
        $this->subject = $user;

        return $this;
    }

    /**
     * Check if there is any restrition for subject's role.
     *
     * @return bool
     */
    protected function checkRestrictions() : bool
    {
        // Let's get Role's restritions and check
        // if its present.
        $restrictions = $this->subject->role->restrictions;

        if (is_null($restrictions)) return true;

        // We need to check if subjects's IP address is allowed
        // by it's Role to perform the action.
        if (isset($restrictions['ip']))
        {
            $ip = ip2long(request()->ip());
            $rule = $restrictions['ip']['rule'];

            switch ($restrictions['ip']['type'])
            {
                case 'one':
                    // Let's check if subject's IP is the same
                    // as set one for Role.
                    $fail = true;

                    foreach ($rule as $ip)
                    {
                        if ($ip == ip2long($ip)) $fail = false;
                    }
                    if ($fail) return false;

                    break;

                case 'range':
                    // Let's check if subject's IP is within
                    // range set to Role.
                    if ($ip < ip2long($rule[0]) || $ip > ip2long($rule[1])) return false;

                    break;

                default:
                    // There's missconfigation here
                    throw new Exception('Bad Role configuration.', 400);
            }
        }

        // We need to check if access hour is correct.
        if (isset($restrictions['hours']))
        {
            $now = now();
            $rule = $restrictions['ip']['rule'];

            switch ($restrictions['hours']['type']) {
                case 'day':
                    if (!in_array($now->dayOfWeekIso, $rule)) return false;

                case 'hour':
                    if ($now->hour < $rule[0] || $now->hour > $rule[1]) return false;
                    break;

                default:
                    // There's missconfigation here
                    throw new Exception('Bad Role configuration.', 400);
            }
        }

        // We need to check if subject is in allowed place.
        if (isset($restrictions['location']))
        {
            // Location goes here
        }

        return true;
    }

    /**
     * Checks if given level is enough.
     *
     * @param  int  $min
     * @return bool
     */
    protected function hasLevel(int $min) : bool
    {
        // By default level is set to 0.
        $level = 0;

        if (in_array($this->privilege, array_keys($this->subject->role->privileges))) {
            // First we get subject level for its role.
            $level = $this->subject->role->privileges[$this->privilege];
        }

        if (in_array($this->privilege, array_keys($this->subject->getPermissions()))) {
            // Second we have to check if there's a personal
            // granted permission for subject.
            // PERSONAL overwrites role's one!
            $level = $this->subject->getPermissions()[$this->privilege];
        }

        return $level >= $min ? true : false;
    }

    /**
     * Determine if give Subject has resource.
     *
     * @param  Illuminate\Database\Eloquent\Model  $model
     * @param  string|null  $relation
     * @return bool
     */
    protected function hasModel(Model $model, $relation) : bool
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
     * @param  Illuminate\Database\Eloquent\Model  $model
     * @param  string|null $foreign
     * @return bool
     */
    protected function isOwner(Model $model, $foreign) : bool
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
     * @param  Illuminate\Database\Eloquent\Model  $model
     * @param  string $modelRelation
     * @param  string $relation
     * @return bool
     */
    protected function isSharing(Model $model, $modelRelation, $relation) : bool
    {
        $foreign = $model->$modelRelation()->getForeignKeyName();

        return $this->subject->$relation()->where($foreign, $model->id)->exists();
    }

    /**
     * Check if detector is correctly set.
     *
     * @return void
     * @throws InvalidArgumentException
     */
    protected function hasSubjectSet()
    {
        if (empty($this->subject))
            throw new InvalidArgumentException('Setting subject is required. Use method subject() befor detection.');
    }

    /**
     * Check if detector is correctly set.
     *
     * @return void
     * @throws InvalidArgumentException
     */
    protected function hasSubjectAndPrivilegeSet()
    {
        if (empty($this->subject) || empty($this->privilege))
            throw new InvalidArgumentException('Setting subject & privilege is required. Use method subject() & privilege() befor detection.');
    }
}

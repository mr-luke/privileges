<?php

namespace Mrluke\Privileges;

/**
 * Manager is a class that provides full complex methods
 * to assign Authorizable to permissions and role.
 *
 * @author    Łukasz Sitnicki (mr-luke)
 * @link      http://github.com/mr-luke/privileges
 *
 * @category  Laravel
 * @package   mr-luke/privileges
 * @license   MIT
 * @version   1.0.0
 */
class Manager
{

    public function __construct()
    {
        // code...
    }

    /**
     * Return Authorizable model reference.
     *
     * @return string
     */
    public function getAuthorizableModel(): string
    {
        return \App\Users::class;
    }

    /**
     * Return Authorizable migration config.
     *
     * @return array
     */
    public function getAuthorizableMigration(): array
    {
        return [
            'key'   => 'id',
            'type'  => 'integer',
            'table' => 'users'
        ];
    }
}

<?php

namespace Mrluke\Privileges\Contracts;

/**
 * Authorizable interface for package.
 *
 * @author    Łukasz Sitnicki (mr-luke)
 * @link      http://github.com/mr-luke/privileges
 *
 * @category  Laravel
 * @package   mr-luke/privileges
 * @license   MIT
 */
interface Authorizable
{
    /**
     * Returns users personal aditional privileges.
     *
     */
    public function permissions();

    /**
     * Return related Roles.
     *
     */
    public function roles();
}

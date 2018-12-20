<?php

namespace Mrluke\Privileges\Contracts;

/**
 * Role interface for package.
 *
 * @author    Łukasz Sitnicki (mr-luke)
 * @link      http://github.com/mr-luke/privileges
 *
 * @category  Laravel
 * @package   mr-luke/privileges
 * @license   MIT
 */
interface Role extends Permitable
{
    /**
     * Returns users assigned to Role.
     *
     */
    public function assigned();

    /**
     * Return parent Role.
     *
     */
    public function parent();

    /**
     * Return Roles assigned as children.
     *
     */
    public function childen();
}

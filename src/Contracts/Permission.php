<?php

namespace Mrluke\Privileges\Contracts;

/**
 * Permission interface for package.
 *
 * @author    Łukasz Sitnicki (mr-luke)
 * @link      http://github.com/mr-luke/privileges
 *
 * @category  Laravel
 * @package   mr-luke/privileges
 * @license   MIT
 */
interface Permission
{
    /**
     * Returns permission level.
     *
     */
    public function getLevel(): int;
}

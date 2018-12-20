<?php

namespace Mrluke\Privileges\Contracts;

/**
 * Permitable interface for package.
 *
 * @author    Łukasz Sitnicki (mr-luke)
 * @link      http://github.com/mr-luke/privileges
 *
 * @category  Laravel
 * @package   mr-luke/privileges
 * @license   MIT
 */
interface Permitable
{
    /**
     * Return related Permission.
     *
     */
    public function permissions();
}

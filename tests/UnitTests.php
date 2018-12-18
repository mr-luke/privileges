<?php

namespace Mrluke\Privileges\Tests;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

use PHPUnit\Framework\TestCase;

/**
 * UnitTests for package.
 *
 * @author    Łukasz Sitnicki (mr-luke)
 * @link      http://github.com/mr-luke/privileges
 * @license   MIT
 */
class UnitTests extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testTemp()
    {
        $this->assertTrue(true);
    }
}

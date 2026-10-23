<?php
declare(strict_types=1);

/**
 * Copyright 2010 - 2026, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2026, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Test\TestCase\Traits;

use Cake\TestSuite\TestCase;
use CakeDC\Users\Traits\RandomStringTrait;

class RandomStringTraitTest extends TestCase
{
    /**
     * @var object
     */
    public object $traitObject;

    public function setUp(): void
    {
        parent::setUp();
        $this->traitObject = new class {
            use RandomStringTrait;
        };
    }

    public function tearDown(): void
    {
        unset($this->traitObject);
        parent::tearDown();
    }

    public function testRandomStringLength(): void
    {
        $this->assertSame(10, strlen($this->traitObject->randomString()));
        $this->assertSame(30, strlen($this->traitObject->randomString(30)));
        $this->assertSame(10, strlen($this->traitObject->randomString(-300)));
        $this->assertSame(10, strlen($this->traitObject->randomString('text')));
    }

    public function testRandomStringUsesSecureRandomness(): void
    {
        $first = $this->traitObject->randomString(32);
        $second = $this->traitObject->randomString(32);

        $this->assertSame(32, strlen($first));
        $this->assertSame(32, strlen($second));
        $this->assertNotSame($first, $second);
        $this->assertMatchesRegularExpression('/^[0-9a-zA-Z]+$/', $first);
        $this->assertMatchesRegularExpression('/^[0-9a-zA-Z]+$/', $second);
    }

    public function testRandomStringOddLength(): void
    {
        $this->assertSame(31, strlen($this->traitObject->randomString(31)));
        $this->assertSame(1, strlen($this->traitObject->randomString(1)));
    }
}

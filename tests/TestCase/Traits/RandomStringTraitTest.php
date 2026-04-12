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

class RandomStringTraitTest extends TestCase
{
    /**
     * @var \CakeDC\Users\Traits\RandomStringTrait|\PHPUnit\Framework\MockObject\MockObject
     */
    public $Trait;

    public function setUp(): void
    {
        parent::setUp();
        $this->Trait = $this->getMockForTrait('CakeDC\Users\Traits\RandomStringTrait');
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    public function testRandomStringLength()
    {
        $this->assertSame(10, strlen($this->Trait->randomString()));
        $this->assertSame(30, strlen($this->Trait->randomString(30)));
        $this->assertSame(10, strlen($this->Trait->randomString('-300')));
        $this->assertSame(10, strlen($this->Trait->randomString('text')));
    }

    public function testRandomStringUsesSecureRandomness()
    {
        $first = $this->Trait->randomString(32);
        $second = $this->Trait->randomString(32);

        $this->assertSame(32, strlen($first));
        $this->assertSame(32, strlen($second));
        $this->assertNotSame($first, $second);
        $this->assertMatchesRegularExpression('/^[0-9a-zA-Z]+$/', $first);
        $this->assertMatchesRegularExpression('/^[0-9a-zA-Z]+$/', $second);
    }

    public function testRandomStringOddLength()
    {
        $this->assertSame(31, strlen($this->Trait->randomString(31)));
        $this->assertSame(1, strlen($this->Trait->randomString(1)));
    }
}

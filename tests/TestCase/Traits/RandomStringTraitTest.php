<?php
declare(strict_types=1);

/**
 * Copyright 2010 - 2019, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2018, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Test\TestCase\Traits;

use Cake\TestSuite\TestCase;

class RandomStringTraitTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->Trait = $this->getMockForTrait('CakeDC\Users\Traits\RandomStringTrait');
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    public function testRandomString()
    {
        $result = $this->Trait->randomString();
        $this->assertEquals(10, strlen($result));

        $result = $this->Trait->randomString(30);
        $this->assertEquals(30, strlen($result));

        $result = $this->Trait->randomString('-300');
        $this->assertEquals(10, strlen($result));

        $result = $this->Trait->randomString('text');
        $this->assertEquals(10, strlen($result));
    }
}

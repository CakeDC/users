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

namespace CakeDC\Users\Test\TestCase\Exception;

use Cake\TestSuite\TestCase;
use CakeDC\Users\Exception\MissingEmailException;

class MissingEmailExceptionTest extends TestCase
{
    /**
     * Setup
     */
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Tear Down
     */
    public function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * Test construct
     */
    public function testConstruct()
    {
        $exception = new MissingEmailException('message');
        $this->assertEquals('message', $exception->getMessage());
    }
}

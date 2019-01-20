<?php
/**
 * Copyright 2010 - 2017, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2017, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Test\TestCase\Auth\Exception;

use CakeDC\Users\Auth\Exception\InvalidProviderException;
use Cake\TestSuite\TestCase;

class InvalidProviderExceptionTest extends TestCase
{
    protected $_messageTemplate = 'Invalid provider or missing class (%s)';
    protected $code = 500;

    /**
     * Setup
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * Tear Down
     */
    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * Test construct
     */
    public function testConstruct()
    {
        $exception = new InvalidProviderException('message');
        $this->assertEquals('message', $exception->getMessage());
    }
}

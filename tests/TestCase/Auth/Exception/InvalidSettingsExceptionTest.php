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

use CakeDC\Users\Auth\Exception\InvalidSettingsException;
use Cake\TestSuite\TestCase;

class InvalidSettingsExceptionTest extends TestCase
{
    protected $_messageTemplate = 'Invalid settings for key (%s)';
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
        $exception = new InvalidSettingsException('message');
        $this->assertEquals('message', $exception->getMessage());
    }
}

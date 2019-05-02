<?php
declare(strict_types=1);
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

use Cake\TestSuite\TestCase;
use CakeDC\Users\Auth\Exception\MissingEventListenerException;

class MissingEventListenerExceptionTest extends TestCase
{
    protected $_messageTemplate = 'Missing listener to the (%s) event.';

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
        $exception = new MissingEventListenerException('message');
        $this->assertEquals('message', $exception->getMessage());
    }
}

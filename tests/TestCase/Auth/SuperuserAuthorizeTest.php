<?php
/**
 * Copyright 2010 - 2015, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2015, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Test\TestCase\Auth;

use CakeDC\Users\Auth\SuperuserAuthorize;
use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\Event\EventManager;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\TestSuite\TestCase;

class SuperuserAuthorizeTest extends TestCase
{

    /**
     * @var SuperuserAuthorize
     */
    protected $superuserAuthorize;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    public function setUp()
    {
        $request = new Request();
        $response = new Response();

        $this->controller = $this->getMock(
            'Cake\Controller\Controller',
            null,
            [$request, $response]
        );
        $registry = new ComponentRegistry($this->controller);
        $this->superuserAuthorize = new SuperuserAuthorize($registry);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    public function tearDown()
    {
        unset($this->superuserAuthorize, $this->controller);
    }

    /**
     * @covers CakeDC\Users\Auth\SuperuserAuthorize::authorize
     */
    public function testAuthorizeIsSuperuser()
    {
        $user = [
            'is_superuser' => true,
        ];
        $request = new Request();
        $result = $this->superuserAuthorize->authorize($user, $request);
        $this->assertTrue($result);
    }

    /**
     * @covers CakeDC\Users\Auth\SuperuserAuthorize::authorize
     */
    public function testAuthorizeIsNotSuperuser()
    {
        $user = [
            'is_superuser' => false,
        ];
        $request = new Request();
        $result = $this->superuserAuthorize->authorize($user, $request);
        $this->assertFalse($result);
    }

    /**
     * @covers CakeDC\Users\Auth\SuperuserAuthorize::authorize
     */
    public function testAuthorizeWeirdUser()
    {
        $request = new Request();
        $user = 'non array';
        $result = $this->superuserAuthorize->authorize($user, $request);
        $this->assertFalse($result);
    }
}

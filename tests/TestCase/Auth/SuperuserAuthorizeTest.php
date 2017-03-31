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

namespace CakeDC\Users\Auth\Test\TestCase\Auth;

use CakeDC\Users\Auth\SuperuserAuthorize;
use Cake\Controller\ComponentRegistry;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
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
        $request = new ServerRequest();
        $response = new Response();

        $this->controller = $this->getMockBuilder('Cake\Controller\Controller')
            ->setMethods(null)
            ->setConstructorArgs([$request, $response])
            ->getMock();
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
        $request = new ServerRequest();
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
        $request = new ServerRequest();
        $result = $this->superuserAuthorize->authorize($user, $request);
        $this->assertFalse($result);
    }

    /**
     * @covers CakeDC\Users\Auth\SuperuserAuthorize::authorize
     */
    public function testAuthorizeWeirdUser()
    {
        $request = new ServerRequest();
        $user = 'non array';
        $result = $this->superuserAuthorize->authorize($user, $request);
        $this->assertFalse($result);
    }
}

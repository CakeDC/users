<?php
/**
 * Copyright 2010 - 2015, Cake Development Corporation (+1 702 425 5085) (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2015, Cake Development Corporation (+1 702 425 5085) (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Test\TestCase\Auth;

use CakeDC\Users\Auth\ApiKeyAuthenticate;
use Cake\Controller\ComponentRegistry;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\TestSuite\TestCase;

class ApiKeyAuthenticateTest extends TestCase
{

    public $fixtures = [
        'plugin.CakeDC/Users.users',
    ];

    /**
     * @var ApiKeyAuthenticate
     */
    public $apiKey;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    public function setUp()
    {
        $request = new Request();
        $response = new Response();

        $controller = $this->getMock(
            'Cake\Controller\Controller',
            null,
            [$request, $response]
        );
        $registry = new ComponentRegistry($controller);
        $this->apiKey = new ApiKeyAuthenticate($registry, ['require_ssl' => false]);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    public function tearDown()
    {
        unset($this->apiKey, $this->controller);
    }

    /**
     * test
     *
     * @return void
     */
    public function testAuthenticateHappy()
    {
        $request = new Request('/?api_key=yyy');
        $result = $this->apiKey->authenticate($request, new Response());
        $this->assertEquals('user-1', $result['username']);
    }

    /**
     * test
     *
     * @return void
     */
    public function testAuthenticateFail()
    {
        $request = new Request('/');
        $result = $this->apiKey->authenticate($request, new Response());
        $this->assertFalse($result);

        $request = new Request('/?api_key=none');
        $result = $this->apiKey->authenticate($request, new Response());
        $this->assertFalse($result);

        $request = new Request('/?api_key=');
        $result = $this->apiKey->authenticate($request, new Response());
        $this->assertFalse($result);
    }

    /**
     * test
     *
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage Type wrong is not valid
     *
     */
    public function testAuthenticateWrongType()
    {
        $this->apiKey->config('type', 'wrong');
        $request = new Request('/');
        $this->apiKey->authenticate($request, new Response());
    }

    /**
     * test
     *
     * @expectedException \Cake\Network\Exception\ForbiddenException
     * @expectedExceptionMessage SSL is required for ApiKey Authentication
     *
     */
    public function testAuthenticateRequireSSL()
    {
        $this->apiKey->config('require_ssl', true);
        $request = new Request('/?api_key=test');
        $this->apiKey->authenticate($request, new Response());
    }

    /**
     * test
     *
     */
    public function testAuthenticateRequireSSLNoKey()
    {
        $this->apiKey->config('require_ssl', true);
        $request = new Request('/');
        $this->assertFalse($this->apiKey->authenticate($request, new Response()));
    }


    /**
     * test
     *
     * @return void
     */
    public function testHeaderHappy()
    {
        $request = $this->getMockBuilder('\Cake\Network\Request')
            ->setMethods(['header'])
            ->getMock();
        $request->expects($this->once())
            ->method('header')
            ->with('api_key')
            ->will($this->returnValue('yyy'));
        $this->apiKey->config('type', 'header');
        $result = $this->apiKey->authenticate($request, new Response());
        $this->assertEquals('user-1', $result['username']);
    }

    /**
     * test
     *
     * @return void
     */
    public function testAuthenticateHeaderFail()
    {
        $request = $this->getMockBuilder('\Cake\Network\Request')
            ->setMethods(['header'])
            ->getMock();
        $request->expects($this->once())
            ->method('header')
            ->with('api_key')
            ->will($this->returnValue('wrong'));
        $this->apiKey->config('type', 'header');
        $result = $this->apiKey->authenticate($request, new Response());
        $this->assertFalse($result);
    }
}

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
use Cake\Core\Configure;
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

        $controller = $this->getMockBuilder('Cake\Controller\Controller')
            ->setMethods(null)
            ->setConstructorArgs([$request, $response])
            ->getMock();
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
        $request = new Request('/?api_key=xxx');
        $result = $this->apiKey->authenticate($request, new Response());
        $this->assertEquals('user-2', $result['username']);
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

        $request = new Request('/?api_key=yyy');
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
            ->will($this->returnValue('xxx'));
        $this->apiKey->config('type', 'header');
        $result = $this->apiKey->authenticate($request, new Response());
        $this->assertEquals('user-2', $result['username']);
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

    /**
     * test
     *
     * @return void
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage Unknown finder method "undefinedInConfig"
     */
    public function testAuthenticateFinderConfig()
    {
        $this->apiKey->config('finder', 'undefinedInConfig');
        $request = new Request('/?api_key=xxx');
        $result = $this->apiKey->authenticate($request, new Response());
    }

    /**
     * test
     *
     * @return void
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage Unknown finder method "undefinedFinderInAuth"
     */
    public function testAuthenticateFinderAuthConfig()
    {
        Configure::write('Auth.authenticate.all.finder', 'undefinedFinderInAuth');
        $request = new Request('/?api_key=xxx');
        $result = $this->apiKey->authenticate($request, new Response());
    }

    /**
     * test
     *
     * @return void
     */
    public function testAuthenticateDefaultAllFinder()
    {
        Configure::write('Auth.authenticate.all.finder', null);
        $request = new Request('/?api_key=yyy');
        $result = $this->apiKey->authenticate($request, new Response());
        $this->assertEquals('user-1', $result['username']);
    }
}

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

namespace Users\Test\TestCase\Auth;

use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\Event\EventManager;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\TestSuite\TestCase;
use Users\Auth\RememberMeAuthenticate;
use Users\Auth\SuperuserAuthorize;

class RememberMeAuthenticateTest extends TestCase
{

    public $fixtures = [
        'plugin.users.users',
    ];

    /**
     * @var RememberMeAuthenticate
     */
    protected $_rememberMe;

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
        $this->rememberMe = new RememberMeAuthenticate($registry);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    public function tearDown()
    {
        unset($this->rememberMe, $this->controller);
    }

    /**
     * test
     *
     * @return void
     */
    public function testAuthenticateHappy()
    {
        $request = new Request('/');
        $request->env('HTTP_USER_AGENT', 'user-agent');
        $mockCookie = $this->getMockBuilder('Cake\Controller\Component\CookieComponent')
                ->disableOriginalConstructor()
                ->setMethods(['check', 'read'])
                ->getMock();
        $mockCookie
                ->expects($this->once())
                ->method('check')
                ->with('remember_me')
                ->will($this->returnValue(true));
        $mockCookie
                ->expects($this->once())
                ->method('read')
                ->with('remember_me')
                ->will($this->returnValue([
                    'id' => 1,
                    'user_agent' => 'user-agent'
                ]));
        $registry = new ComponentRegistry($this->controller);
        $registry->set('Cookie', $mockCookie);
        $this->rememberMe = new RememberMeAuthenticate($registry);
        $result = $this->rememberMe->authenticate($request, new Response());
        $this->assertEquals('user-1', $result['username']);
    }

    /**
     * test
     *
     * @return void
     */
    public function testAuthenticateBadUser()
    {
        $request = new Request('/');
        $request->env('HTTP_USER_AGENT', 'user-agent');
        $mockCookie = $this->getMockBuilder('Cake\Controller\Component\CookieComponent')
                ->disableOriginalConstructor()
                ->setMethods(['check', 'read'])
                ->getMock();
        $mockCookie
                ->expects($this->once())
                ->method('check')
                ->with('remember_me')
                ->will($this->returnValue(true));
        $mockCookie
                ->expects($this->once())
                ->method('read')
                ->with('remember_me')
                ->will($this->returnValue([
                    'id' => 'bad-user',
                    'user_agent' => 'user-agent'
                ]));
        $registry = new ComponentRegistry($this->controller);
        $registry->set('Cookie', $mockCookie);
        $this->rememberMe = new RememberMeAuthenticate($registry);
        $result = $this->rememberMe->authenticate($request, new Response());
        $this->assertFalse($result);
    }


    /**
     * test
     *
     * @return void
     */
    public function testAuthenticateBadAgent()
    {
        $request = new Request('/');
        $request->env('HTTP_USER_AGENT', 'user-agent');
        $mockCookie = $this->getMockBuilder('Cake\Controller\Component\CookieComponent')
                ->disableOriginalConstructor()
                ->setMethods(['check', 'read'])
                ->getMock();
        $mockCookie
                ->expects($this->once())
                ->method('check')
                ->with('remember_me')
                ->will($this->returnValue(true));
        $mockCookie
                ->expects($this->once())
                ->method('read')
                ->with('remember_me')
                ->will($this->returnValue([
                    'id' => 1,
                    'user_agent' => 'bad-agent'
                ]));
        $registry = new ComponentRegistry($this->controller);
        $registry->set('Cookie', $mockCookie);
        $this->rememberMe = new RememberMeAuthenticate($registry);
        $result = $this->rememberMe->authenticate($request, new Response());
        $this->assertFalse($result);
    }

    /**
     * test
     *
     * @return void
     */
    public function testAuthenticateNoCookie()
    {
        $request = new Request('/');
        $request->env('HTTP_USER_AGENT', 'user-agent');
        $mockCookie = $this->getMockBuilder('Cake\Controller\Component\CookieComponent')
                ->disableOriginalConstructor()
                ->setMethods(['check', 'read'])
                ->getMock();
        $mockCookie
                ->expects($this->once())
                ->method('check')
                ->with('remember_me')
                ->will($this->returnValue(false));
        $mockCookie
                ->expects($this->never())
                ->method('read');

        $registry = new ComponentRegistry($this->controller);
        $registry->set('Cookie', $mockCookie);
        $this->rememberMe = new RememberMeAuthenticate($registry);
        $result = $this->rememberMe->authenticate($request, new Response());
        $this->assertFalse($result);
    }
}

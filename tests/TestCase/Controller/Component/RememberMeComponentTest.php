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

namespace CakeDC\Users\Test\TestCase\Controller\Component;

use CakeDC\Users\Controller\Component\RememberMeComponent;
use Cake\Controller\ComponentRegistry;
use Cake\Controller\Component\AuthComponent;
use Cake\Controller\Component\CookieComponent;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Network\Request;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use Cake\Utility\Security;
use InvalidArgumentException;

/**
 * Users\Controller\Component\RememberMeComponent Test Case
 */
class RememberMeComponentTest extends TestCase
{

    public $fixtures = [
        'plugin.CakeDC/Users.users'
    ];
    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        Security::salt('2a20bac195a9eb2e28f05b7ac7090afe599365a8fe480b7d8a5ce0f79687346e');
        $this->request = new Request('controller_posts/index');
        $this->request->params['pass'] = [];
        $this->controller = $this->getMockBuilder('Cake\Controller\Controller')
                ->setMethods(['redirect'])
                ->setConstructorArgs([$this->request])
                ->getMock();
        $this->registry = new ComponentRegistry($this->controller);
        $this->rememberMeComponent = new RememberMeComponent($this->registry, []);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->rememberMeComponent);

        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize()
    {
        $cookieOptions = [
            'expires' => '1 month',
            'httpOnly' => true,
            'path' => '',
            'domain' => '',
            'secure' => false,
            'key' => '2a20bac195a9eb2e28f05b7ac7090afe599365a8fe480b7d8a5ce0f79687346e',
            'encryption' => 'aes',
            'enabled' => false
        ];
        $this->assertEquals($cookieOptions, $this->rememberMeComponent->Cookie->configKey('remember_me'));
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitializeException()
    {
        $salt = Security::salt();
        Security::salt('too small');
        try {
            $this->rememberMeComponent = new RememberMeComponent($this->registry, []);
        } catch (InvalidArgumentException $ex) {
            $this->assertEquals('Invalid app salt, app salt must be at least 256 bits (32 bytes) long', $ex->getMessage());
        }

        Security::salt($salt);
    }

    /**
     * Test
     *
     * @return void
     */
    public function testSetLoginCookie()
    {
        $event = new Event('event');
        $this->rememberMeComponent->Auth = $this->getMockBuilder('Cake\Controller\Component\AuthComponent')
            ->setMethods(['user'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->rememberMeComponent->Auth->expects($this->once())
            ->method('user')
            ->with('id')
            ->will($this->returnValue(1));
        $this->rememberMeComponent->Cookie = $this->getMockBuilder('Cake\Controller\Component\CookieComponent')
            ->setMethods(['write'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->rememberMeComponent->request = (new Request('/'))->env('HTTP_USER_AGENT', 'user-agent');
        $this->rememberMeComponent->Cookie->expects($this->once())
            ->method('write')
            ->with('remember_me', ['id' => 1, 'user_agent' => 'user-agent']);
        $this->rememberMeComponent->setLoginCookie($event);
    }

    /**
     * Test
     *
     * @return void
     */
    public function testBeforeFilter()
    {
        $event = new Event('event');
        $this->rememberMeComponent->Auth = $this->getMockBuilder('Cake\Controller\Component\AuthComponent')
            ->setMethods(['user', 'identify', 'setUser', 'redirectUrl'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->rememberMeComponent->Auth->expects($this->once())
            ->method('user');
        $user = ['id' => 1];
        $this->rememberMeComponent->Auth->expects($this->once())
            ->method('identify')
            ->will($this->returnValue($user));
        $this->rememberMeComponent->Auth->expects($this->once())
            ->method('setUser')
            ->with($user);
        $this->rememberMeComponent->Auth->expects($this->once())
            ->method('redirectUrl')
            ->will($this->returnValue('/login'));
        $this->controller->expects($this->once())
                ->method('redirect')
                ->with('/login');
        $this->rememberMeComponent->beforeFilter($event);
    }

    /**
     * Test
     *
     * @return void
     */
    public function testBeforeFilterNotIdentified()
    {
        $event = new Event('event');
        $this->rememberMeComponent->Auth = $this->getMockBuilder('Cake\Controller\Component\AuthComponent')
            ->setMethods(['user', 'identify', 'setUser', 'redirectUrl'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->rememberMeComponent->Auth->expects($this->at(0))
            ->method('user');
        $this->rememberMeComponent->Auth->expects($this->at(1))
            ->method('identify');

        $this->assertNull($this->rememberMeComponent->beforeFilter($event));
    }

    /**
     * Test
     *
     * @return void
     */
    public function testBeforeFilterUserLoggedIn()
    {
        $event = new Event('event');
        $this->rememberMeComponent->Auth = $this->getMockBuilder('Cake\Controller\Component\AuthComponent')
            ->setMethods(['user', 'identify', 'setUser', 'redirectUrl'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->rememberMeComponent->Auth->expects($this->once())
            ->method('user')
            ->will($this->returnValue([
                'id' => 1,
            ]));
        $this->assertNull($this->rememberMeComponent->beforeFilter($event));
    }
}

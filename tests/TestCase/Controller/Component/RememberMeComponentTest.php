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

namespace CakeDC\Users\Test\TestCase\Controller\Component;

use CakeDC\Users\Controller\Component\RememberMeComponent;
use Cake\Controller\ComponentRegistry;
use Cake\Controller\Component\AuthComponent;
use Cake\Controller\Component\CookieComponent;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Http\ServerRequest;
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
        $this->request = new ServerRequest('controller_posts/index');
        $this->request = $this->request->withParam('pass', []);
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
        $this->rememberMeComponent->request = (new ServerRequest('/'))->env('HTTP_USER_AGENT', 'user-agent');
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
        $this->controller->expects($this->once())
                ->method('redirect')
                ->with('/controller_posts/index');
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

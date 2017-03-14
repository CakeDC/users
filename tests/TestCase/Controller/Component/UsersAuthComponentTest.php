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

use CakeDC\Users\Controller\Component\UsersAuthComponent;
use CakeDC\Users\Exception\MissingEmailException;
use CakeDC\Users\Exception\UserNotFoundException;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Database\Exception;
use Cake\Event\Event;
use Cake\Network\Request;
use Cake\Network\Session;
use Cake\ORM\Entity;
use Cake\Routing\Exception\MissingRouteException;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use Cake\Utility\Security;

/**
 * Users\Controller\Component\UsersAuthComponent Test Case
 */
class UsersAuthComponentTest extends TestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.CakeDC/Users.users',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->backupUsersConfig = Configure::read('Users');

        Router::reload();
        Plugin::routes('CakeDC/Users');
        Router::connect('/route/*', [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'requestResetPassword'
        ]);
        Router::connect('/notAllowed/*', [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'edit'
        ]);
        Security::salt('YJfIxfs2guVoUubWDYhG93b0qyJfIxfs2guwvniR2G0FgaC9mi');
        Configure::write('App.namespace', 'Users');
        $this->request = $this->getMockBuilder('Cake\Network\Request')
            ->setMethods(['is', 'method'])
            ->getMock();
        $this->request->expects($this->any())->method('is')->will($this->returnValue(true));
        $this->response = $this->getMockBuilder('Cake\Network\Response')
            ->setMethods(['stop'])
            ->getMock();
        $this->Controller = new Controller($this->request, $this->response);
        $this->Registry = $this->Controller->components();
        $this->Controller->UsersAuth = new UsersAuthComponent($this->Registry);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        $_SESSION = [];
        unset($this->Controller, $this->UsersAuth);
        Configure::write('Users', $this->backupUsersConfig);
    }

    /**
     * Test initialize
     *
     */
    public function testInitialize()
    {
        $this->Registry->unload('Auth');
        $this->Controller->UsersAuth = new UsersAuthComponent($this->Registry);
        $this->assertInstanceOf('CakeDC\Users\Controller\Component\UsersAuthComponent', $this->Controller->UsersAuth);
    }

    /**
     * Test initialize with not rememberMe component needed
     *
     */
    public function testInitializeNoRequiredRememberMe()
    {
        Configure::write('Users.RememberMe.active', false);
        $class = 'CakeDC\Users\Controller\Component\UsersAuthComponent';
        $this->Controller->UsersAuth = $this->getMockBuilder($class)
            ->setMethods(['_loadRememberMe', '_initAuth', '_loadSocialLogin', '_attachPermissionChecker'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->Controller->UsersAuth->expects($this->once())
            ->method('_initAuth');
        $this->Controller->UsersAuth->expects($this->never())
            ->method('_loadRememberMe');
        $this->Controller->UsersAuth->initialize([]);
    }

    /**
     * test
     *
     * @return void
     */
    public function testIsUrlAuthorizedUserNotLoggedIn()
    {
        $event = new Event('event');
        $event->data = [
            'url' => '/route',
        ];
        $this->Controller->Auth = $this->getMockBuilder('Cake\Controller\Component\AuthComponent')
            ->setMethods(['user', 'isAuthorized'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->Controller->Auth->expects($this->once())
            ->method('user')
            ->will($this->returnValue(false));
        $result = $this->Controller->UsersAuth->isUrlAuthorized($event);
        $this->assertFalse($result);
    }

    /**
     * test The user is not logged in, but the controller action is public $this->Auth->allow()
     *
     * @return void
     */
    public function testIsUrlAuthorizedUserNotLoggedInActionAllowed()
    {
        $event = new Event('event');
        $event->data = [
            'url' => '/route',
        ];
        $this->Controller->Auth = $this->getMockBuilder('Cake\Controller\Component\AuthComponent')
            ->setMethods(['user', 'isAuthorized'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->Controller->Auth->allowedActions = ['requestResetPassword'];
        $this->Controller->Auth->expects($this->never())
            ->method('user');
        $result = $this->Controller->UsersAuth->isUrlAuthorized($event);
        $this->assertTrue($result);
    }

    /**
     * test The user is logged in and not allowed by rules to access this action,
     * but the controller action is public $this->Auth->allow()
     *
     * @return void
     */
    public function testIsUrlAuthorizedUserLoggedInNotAllowedActionAllowed()
    {
        $event = new Event('event');
        $event->data = [
            'url' => '/notAllowed',
        ];
        $this->Controller->Auth = $this->getMockBuilder('Cake\Controller\Component\AuthComponent')
            ->setMethods(['user', 'isAuthorized'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->Controller->Auth->allowedActions = ['edit'];
        $this->Controller->Auth->expects($this->never())
            ->method('user');
        $result = $this->Controller->UsersAuth->isUrlAuthorized($event);
        $this->assertTrue($result);
    }

    /**
     * test The user is logged in and allowed by rules to access this action,
     * and the controller action is public $this->Auth->allow()
     *
     * @return void
     */
    public function testIsUrlAuthorizedUserLoggedInAllowedActionAllowed()
    {
        $event = new Event('event');
        $event->data = [
            'url' => '/route',
        ];
        $this->Controller->Auth = $this->getMockBuilder('Cake\Controller\Component\AuthComponent')
            ->setMethods(['user', 'isAuthorized'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->Controller->Auth->allowedActions = ['requestResetPassword'];
        $this->Controller->Auth->expects($this->never())
            ->method('user');
        $result = $this->Controller->UsersAuth->isUrlAuthorized($event);
        $this->assertTrue($result);
    }

    /**
     * test
     *
     * @return void
     */
    public function testIsUrlAuthorizedNoUrl()
    {
        $event = new Event('event');
        $this->Controller->Auth = $this->getMockBuilder('Cake\Controller\Component\AuthComponent')
            ->setMethods(['user', 'isAuthorized'])
            ->disableOriginalConstructor()
            ->getMock();
        $result = $this->Controller->UsersAuth->isUrlAuthorized($event);
        $this->assertFalse($result);
    }

    /**
     * test
     *
     * @return void
     */
    public function testIsUrlAuthorizedUrlRelativeString()
    {
        $event = new Event('event');
        $event->data = [
            'url' => '/route',
        ];
        $this->Controller->Auth = $this->getMockBuilder('Cake\Controller\Component\AuthComponent')
            ->setMethods(['user', 'isAuthorized'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->Controller->Auth->expects($this->once())
            ->method('user')
            ->will($this->returnValue(['id' => 1]));
        $this->Controller->Auth->expects($this->once())
            ->method('isAuthorized')
            ->with(null, $this->callback(function ($subject) {
                return $subject->params === [
                        'plugin' => 'CakeDC/Users',
                        'controller' => 'Users',
                        'action' => 'requestResetPassword',
                        '_ext' => null,
                        'pass' => [],
                        '_matchedRoute' => '/route/*',
                    ];
            }))
            ->will($this->returnValue(true));
        $result = $this->Controller->UsersAuth->isUrlAuthorized($event);
        $this->assertTrue($result);
    }

    /**
     * test
     *
     * @return void
     * @expectedException Cake\Routing\Exception\MissingRouteException
     */
    public function testIsUrlAuthorizedMissingRouteString()
    {
        $event = new Event('event');
        $event->data = [
            'url' => '/missingRoute',
        ];
        $this->Controller->Auth = $this->getMockBuilder('Cake\Controller\Component\AuthComponent')
            ->setMethods(['user', 'isAuthorized'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->Controller->Auth->expects($this->never())
            ->method('user');
        $result = $this->Controller->UsersAuth->isUrlAuthorized($event);
    }

    /**
     * test
     *
     * @return void
     * @expectedException Cake\Routing\Exception\MissingRouteException
     */
    public function testIsUrlAuthorizedMissingRouteArray()
    {
        $event = new Event('event');
        $event->data = [
            'url' => [
                'controller' => 'missing',
                'action' => 'missing',
            ],
        ];
        $this->Controller->Auth = $this->getMockBuilder('Cake\Controller\Component\AuthComponent')
            ->setMethods(['user', 'isAuthorized'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->Controller->Auth->expects($this->never())
            ->method('user');
        $result = $this->Controller->UsersAuth->isUrlAuthorized($event);
    }

    /**
     * test
     *
     * @return void
     */
    public function testIsUrlAuthorizedUrlAbsoluteForCurrentAppString()
    {
        $event = new Event('event');
        $event->data = [
            'url' => Router::fullBaseUrl() . '/route',
        ];
        $this->Controller->Auth = $this->getMockBuilder('Cake\Controller\Component\AuthComponent')
            ->setMethods(['user', 'isAuthorized'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->Controller->Auth->expects($this->once())
            ->method('user')
            ->will($this->returnValue(['id' => 1]));
        $this->Controller->Auth->expects($this->once())
            ->method('isAuthorized')
            ->with(null, $this->callback(function ($subject) {
                return $subject->params === [
                        'plugin' => 'CakeDC/Users',
                        'controller' => 'Users',
                        'action' => 'requestResetPassword',
                        '_ext' => null,
                        'pass' => [],
                        '_matchedRoute' => '/route/*',
                    ];
            }))
            ->will($this->returnValue(true));
        $result = $this->Controller->UsersAuth->isUrlAuthorized($event);
        $this->assertTrue($result);
    }

    /**
     * test
     *
     * @return void
     */
    public function testIsUrlAuthorizedUrlRelativeForCurrentAppString()
    {
        $event = new Event('event');
        $event->data = [
            'url' => 'route',
        ];
        $this->Controller->Auth = $this->getMockBuilder('Cake\Controller\Component\AuthComponent')
            ->setMethods(['user', 'isAuthorized'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->Controller->Auth->expects($this->once())
            ->method('user')
            ->will($this->returnValue(['id' => 1]));
        $this->Controller->Auth->expects($this->once())
            ->method('isAuthorized')
            ->with(null, $this->callback(function ($subject) {
                return $subject->params === [
                        'plugin' => 'CakeDC/Users',
                        'controller' => 'Users',
                        'action' => 'requestResetPassword',
                        '_ext' => null,
                        'pass' => [],
                        '_matchedRoute' => '/route/*',
                    ];
            }))
            ->will($this->returnValue(true));
        $result = $this->Controller->UsersAuth->isUrlAuthorized($event);
        $this->assertTrue($result);
    }

    /**
     *
     *
     * @return void
     */
    public function testIsUrlAuthorizedUrlAbsoluteForOtherAppString()
    {
        $event = new Event('event');
        $event->data = [
            'url' => 'http://example.com',
        ];
        $this->Controller->Auth = $this->getMockBuilder('Cake\Controller\Component\AuthComponent')
            ->setMethods(['user', 'isAuthorized'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->Controller->Auth->expects($this->never())
            ->method('user');
        $result = $this->Controller->UsersAuth->isUrlAuthorized($event);
        $this->assertTrue($result);
    }

    /**
     * test
     *
     * @return void
     */
    public function testIsUrlAuthorizedUrlArray()
    {
        $event = new Event('event');
        $event->data = [
            'url' => [
                'plugin' => 'CakeDC/Users',
                'controller' => 'Users',
                'action' => 'requestResetPassword',
                'pass-one'
            ],
        ];
        $this->Controller->Auth = $this->getMockBuilder('Cake\Controller\Component\AuthComponent')
            ->setMethods(['user', 'isAuthorized'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->Controller->Auth->expects($this->once())
            ->method('user')
            ->will($this->returnValue(['id' => 1]));
        $this->Controller->Auth->expects($this->once())
            ->method('isAuthorized')
            ->with(null, $this->callback(function ($subject) {
                return $subject->params === [
                        'plugin' => 'CakeDC/Users',
                        'controller' => 'Users',
                        'action' => 'requestResetPassword',
                        '_ext' => null,
                        'pass' => ['pass-one'],
                        '_matchedRoute' => '/route/*',
                    ];
            }))
            ->will($this->returnValue(true));
        $result = $this->Controller->UsersAuth->isUrlAuthorized($event);
        $this->assertTrue($result);
    }
}

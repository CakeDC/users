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
        Security::salt('YJfIxfs2guVoUubWDYhG93b0qyJfIxfs2guwvniR2G0FgaC9mi');
        Configure::write('App.namespace', 'Users');
        $this->request = $this->getMock('Cake\Network\Request', ['is', 'method']);
        $this->request->expects($this->any())->method('is')->will($this->returnValue(true));
        $this->response = $this->getMock('Cake\Network\Response', ['stop']);
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
        $this->Controller->UsersAuth->expects($this->once())
                ->method('_loadSocialLogin');
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
        $this->Controller->Auth->expects($this->once())
                ->method('user')
                ->will($this->returnValue(['id' => 1]));
        $result = $this->Controller->UsersAuth->isUrlAuthorized($event);
        $this->assertFalse($result);
    }

    /**
     * test
     *
     * @return void
     */
    public function testIsUrlAuthorizedUrlString()
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
        $request = new Request('/route');
        $request->params = [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'requestResetPassword',
            'pass' => [],
        ];
        $this->Controller->Auth->expects($this->once())
                ->method('isAuthorized')
                ->with(null, $request)
                ->will($this->returnValue(true));
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
        $request = new Request('/route/pass-one');
        $request->params = [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'requestResetPassword',
            'pass' => ['pass-one'],
        ];
        $this->Controller->Auth->expects($this->once())
                ->method('isAuthorized')
                ->with(null, $request)
                ->will($this->returnValue(true));
        $result = $this->Controller->UsersAuth->isUrlAuthorized($event);
        $this->assertTrue($result);
    }
}

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

namespace CakeDC\Users\Test\TestCase\View\Helper;

use CakeDC\Users\View\Helper\UserHelper;
use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Event\Event;
use Cake\Network\Request;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use Cake\View\View;

/**
 * Users\View\Helper\UserHelper Test Case
 */
class UserHelperTest extends TestCase
{

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        Plugin::routes('CakeDC/Users');
        $this->View = $this->getMock('Cake\View\View', ['append']);
        $this->User = new UserHelper($this->View);
        $this->request = new Request();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->User);

        parent::tearDown();
    }

    /**
     * Test facebookLogin
     *
     * @return void
     */
    public function testFacebookLogin()
    {
        $result = $this->User->facebookLogin();
        $expected = '<a href="/auth/facebook" class="btn btn-social btn-facebook"><i class="fa fa-facebook"></i>Sign in with Facebook</a>';
        $this->assertEquals($expected, $result);
    }

    /**
     * Test twitterLogin
     *
     * @return void
     */
    public function testTwitterLoginEnabled()
    {
        $result = $this->User->twitterLogin();
        $expected = '<a href="/auth/twitter" class="btn btn-social btn-twitter"><i class="fa fa-twitter"></i>Sign in with Twitter</a>';
        $this->assertEquals($expected, $result);
    }

    /**
     * Test twitterLogin
     *
     * @return void
     */
    public function testLogout()
    {
        $result = $this->User->logout();
        $expected = '<a href="/logout">Logout</a>';
        $this->assertEquals($expected, $result);
    }

    /**
     * Test twitterLogin
     *
     * @return void
     */
    public function testLogoutDifferentMessage()
    {
        $result = $this->User->logout('Sign Out');
        $expected = '<a href="/logout">Sign Out</a>';
        $this->assertEquals($expected, $result);
    }

    /**
     * Test twitterLogin
     *
     * @return void
     */
    public function testLogoutWithOptions()
    {
        $result = $this->User->logout('Sign Out', ['class' => 'logout']);
        $expected = '<a href="/logout" class="logout">Sign Out</a>';
        $this->assertEquals($expected, $result);
    }

    /**
     * Test link
     *
     * @return void
     */
    public function testLinkFalse()
    {
        $link = $this->User->link('title', ['controller' => 'noaccess']);
        $this->assertSame(false, $link);
    }

    /**
     * Test link
     *
     * @return void
     */
    public function testLinkAuthorized()
    {
        $view = new View();
        $eventManagerMock = $this->getMockBuilder('Cake\Event\EventManager')
                ->setMethods(['dispatch'])
                ->getMock();
        $view->eventManager($eventManagerMock);
        $this->User = new UserHelper($view);
        $result = new Event('dispatch-result');
        $result->result = true;
        $eventManagerMock->expects($this->once())
                ->method('dispatch')
                ->will($this->returnValue($result));

        $link = $this->User->link('title', '/', ['before' => 'before_', 'after' => '_after', 'class' => 'link-class']);
        $this->assertSame('before_<a href="/" class="link-class">title</a>_after', $link);
    }

    /**
     * Test link
     *
     * @return void
     */
    public function testWelcome()
    {
        $session = $this->getMock('Cake\Network\Session', ['read']);
        $session->expects($this->at(0))
            ->method('read')
            ->with('Auth.User.id')
            ->will($this->returnValue(2));

        $session->expects($this->at(1))
            ->method('read')
            ->with('Auth.User.first_name')
            ->will($this->returnValue('david'));

        $this->User->request = $this->getMock('Cake\Network\Request', ['session']);
        $this->User->request->expects($this->any())
            ->method('session')
            ->will($this->returnValue($session));

        $expected = '<span class="welcome">Welcome, <a href="/profile">david</a></span>';
        $result = $this->User->welcome();
        $this->assertEquals($expected, $result);
    }

    /**
     * Test link
     *
     * @return void
     */
    public function testWelcomeNotLoggedInUser()
    {
        $session = $this->getMock('Cake\Network\Session', ['read']);
        $session->expects($this->at(0))
            ->method('read')
            ->with('Auth.User.id')
            ->will($this->returnValue(null));

        $this->User->request = $this->getMock('Cake\Network\Request', ['session']);
        $this->User->request->expects($this->any())
            ->method('session')
            ->will($this->returnValue($session));

        $result = $this->User->welcome();
        $this->assertEmpty($result);
    }

    /**
     * Test add ReCaptcha field
     *
     * @return void
     */
    public function testAddReCaptcha()
    {
        $siteKey = Configure::read('reCaptcha.key');
        Configure::write('reCaptcha.key', 'testKey');
        $result = $this->User->addReCaptcha();
        $this->assertEquals('<div class="g-recaptcha" data-sitekey="testKey"></div>', $result);
        Configure::write('reCaptcha.key', $siteKey);
    }


    /**
     * Test add ReCaptcha field
     *
     * @return void
     */
    public function testAddReCaptchaScript()
    {
        $this->View->expects($this->at(0))
            ->method('append')
            ->with('script', $this->stringContains('https://www.google.com/recaptcha/api.js'));
        $this->User->addReCaptchaScript();
    }
}

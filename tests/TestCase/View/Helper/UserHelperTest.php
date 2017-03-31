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

namespace CakeDC\Users\Test\TestCase\View\Helper;

use CakeDC\Users\View\Helper\UserHelper;
use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Event\Event;
use Cake\Http\ServerRequest;
use Cake\I18n\I18n;
use Cake\Network\Request;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use Cake\View\Helper\HtmlHelper;
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
        $this->View = $this->getMockBuilder('Cake\View\View')
                ->setMethods(['append'])
                ->getMock();
        //Assuming all these url's are authorized
        $this->AuthLink = $this->getMockBuilder('CakeDC\Users\View\Helper\AuthLinkHelper')
                ->setMethods(['isAuthorized'])
                ->setConstructorArgs([$this->View])
                ->getMock();
        $this->AuthLink->expects($this->any())
            ->method('isAuthorized')
            ->will($this->returnValue(true));
        $this->User = new UserHelper($this->View);
        $this->User->AuthLink = $this->AuthLink;
        $this->request = new ServerRequest();
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
    public function testWelcome()
    {
        $session = $this->getMockBuilder('Cake\Network\Session')
                ->setMethods(['read'])
                ->getMock();
        $session->expects($this->at(0))
            ->method('read')
            ->with('Auth.User.id')
            ->will($this->returnValue(2));

        $session->expects($this->at(1))
            ->method('read')
            ->with('Auth.User.first_name')
            ->will($this->returnValue('david'));

        $this->User->request = $this->getMockBuilder('Cake\Network\Request')
                ->setMethods(['session'])
                ->getMock();
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
        $session = $this->getMockBuilder('Cake\Network\Session')
                ->setMethods(['read'])
                ->getMock();
        $session->expects($this->at(0))
            ->method('read')
            ->with('Auth.User.id')
            ->will($this->returnValue(null));

        $this->User->request = $this->getMockBuilder('Cake\Network\Request')
                ->setMethods(['session'])
                ->getMock();
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
        Configure::write('Users.reCaptcha.key', 'testKey');
        $result = $this->User->addReCaptcha();
        $this->assertEquals('<div class="g-recaptcha" data-sitekey="testKey"></div>', $result);
    }

    /**
     * Test add ReCaptcha field
     *
     * @return void
     */
    public function testAddReCaptchaEmpty()
    {
        $result = $this->User->addReCaptcha();
        $expected = '<p>reCaptcha is not configured! Please configure Users.reCaptcha.key</p>';
        $this->assertEquals($expected, $result);
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

    /**
     * Test social login link
     *
     * @return void
     */
    public function testSocialLoginLink()
    {
        $result = $this->User->socialLogin('facebook');
        $this->assertEquals('<a href="/auth/facebook" class="btn btn-social btn-facebook "><i class="fa fa-facebook"></i>Sign in with Facebook</a>', $result);

        $result = $this->User->socialLogin('twitter', ['label' => 'Register with']);
        $this->assertEquals('<a href="/auth/twitter" class="btn btn-social btn-twitter "><i class="fa fa-twitter"></i>Register with Twitter</a>', $result);
    }

    /**
     * test
     *
     * @return void
     */
    public function testSocialLoginTranslation()
    {
        I18n::locale('es_ES');
        $result = $this->User->socialLogin('facebook');
        $this->assertEquals('<a href="/auth/facebook" class="btn btn-social btn-facebook"><i class="fa fa-facebook"></i>Iniciar sesión con Facebook</a>', $result);
        I18n::locale('en_US');
    }
}

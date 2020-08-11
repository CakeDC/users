<?php
declare(strict_types=1);

/**
 * Copyright 2010 - 2019, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2018, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Test\TestCase\View\Helper;

use Authentication\Identity;
use Cake\Core\Configure;
use Cake\Http\ServerRequest;
use Cake\I18n\I18n;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use CakeDC\Users\Model\Entity\SocialAccount;
use CakeDC\Users\View\Helper\UserHelper;

/**
 * Users\View\Helper\UserHelper Test Case
 */
class UserHelperTest extends TestCase
{
    /**
     * Keep the original config for oauth
     *
     * @var array
     */
    private $oauthConfig;

    /**
     * Keep original config Users.Social.login
     *
     * @var bool
     */
    private $socialLogin;

    /**
     * @var \CakeDC\Users\View\Helper\UserHelper
     */
    private $User;

    /**
     * @var \CakeDC\Users\View\Helper\AuthLinkHelper
     */
    private $AuthLink;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        if ($this->oauthConfig === null) {
            $this->oauthConfig = (array)Configure::read('OAuth');
            $this->socialLogin = Configure::read('Users.Social.login');
        }

        parent::setUp();
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
    public function tearDown(): void
    {
        Configure::write('OAuth', $this->oauthConfig);
        Configure::write('Users.Social.login', $this->socialLogin);
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
        Router::connect('/logout', [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'logout',
        ]);

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
        Router::connect('/logout', [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'logout',
        ]);

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
        Router::connect('/logout', [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'logout',
        ]);

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
        Router::connect('/profile', [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'profile',
        ]);

        $user = [
            'id' => 2,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'username' => 'j04n.d09',
        ];
        $identity = new Identity($user);
        $request = new ServerRequest();
        $request = $request->withAttribute('identity', $identity);
        $this->User->getView()->setRequest($request);
        $expected = '<span class="welcome">Welcome, <a href="/profile">John</a></span>';
        $result = $this->User->welcome();
        $this->assertEquals($expected, $result);
    }

    /**
     * Test link
     *
     * @return void
     */
    public function testWelcomeWillDisplayUsernameInstead()
    {
        Router::connect('/profile', [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'profile',
        ]);

        $user = [
            'id' => 2,
            'last_name' => 'Doe',
            'username' => 'j04n.d09',
        ];
        $identity = new Identity($user);
        $request = new ServerRequest();
        $request = $request->withAttribute('identity', $identity);
        $this->User->getView()->setRequest($request);
        $expected = '<span class="welcome">Welcome, <a href="/profile">j04n.d09</a></span>';
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
        $request = new ServerRequest();
        $this->User->getView()->setRequest($request);
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
        Configure::write('Users.reCaptcha.theme', 'light');
        Configure::write('Users.reCaptcha.size', 'normal');
        Configure::write('Users.reCaptcha.tabindex', '3');
        $this->User->Form->create();
        $result = $this->User->addReCaptcha();
        $this->assertEquals('<div class="g-recaptcha" data-sitekey="testKey" data-theme="light" data-size="normal" data-tabindex="3"></div>', $result);
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
        $this->assertEquals('<a href="/auth/facebook" class="btn btn-social btn-facebook"><i class="fa fa-facebook"></i>Sign in with Facebook</a>', $result);

        $result = $this->User->socialLogin('twitter', ['label' => 'Register with']);
        $this->assertEquals('<a href="/auth/twitter" class="btn btn-social btn-twitter"><i class="fa fa-twitter"></i>Register with Twitter</a>', $result);
    }

    /**
     * test
     *
     * @return void
     */
    public function testSocialLoginTranslation()
    {
        I18n::setLocale('es_ES');
        $result = $this->User->socialLogin('facebook');
        $this->assertEquals('<a href="/auth/facebook" class="btn btn-social btn-facebook"><i class="fa fa-facebook"></i>Iniciar sesión con Facebook</a>', $result);
        I18n::setLocale('en_US');
    }

    /**
     * Test social connect link list
     *
     * @return void
     */
    public function testSocialConnectLinkList()
    {
        Configure::write('Users.Social.login', true);

        Configure::write('OAuth.providers.facebook.options.clientId', 'testclientidtestclientid');
        Configure::write('OAuth.providers.facebook.options.clientSecret', 'testclientsecrettestclientsecret');

        Configure::write('OAuth.providers.google.options.clientId', 'testclientidgoogtestclientidgoog');
        Configure::write('OAuth.providers.google.options.clientSecret', 'testclientsecretgoogtestclientsecretgoog');

        $actual = $this->User->socialConnectLinkList();
        $expected = '<a href="/link-social/facebook" class="btn btn-social btn-facebook"><span class="fa fa-facebook"></span> Connect with Facebook</a>';
        $expected .= '<a href="/link-social/google" class="btn btn-social btn-google"><span class="fa fa-google"></span> Connect with Google</a>';
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test social connect link list, user is connected with facebook
     *
     * @return void
     */
    public function testSocialConnectLinkListIsConnectedWithFacebook()
    {
        Configure::write('Users.Social.login', true);

        Configure::write('OAuth.providers.facebook.options.clientId', 'testclientidtestclientid');
        Configure::write('OAuth.providers.facebook.options.clientSecret', 'testclientsecrettestclientsecret');

        Configure::write('OAuth.providers.google.options.clientId', 'testclientidgoogtestclientidgoog');
        Configure::write('OAuth.providers.google.options.clientSecret', 'testclientsecretgoogtestclientsecretgoog');

        $socialAccounts = [
            new SocialAccount([
                'id' => '00000000-0000-0000-0000-000000000001',
                'user_id' => '00000000-0000-0000-0000-000000000001',
                'provider' => 'Facebook',
                'username' => 'user-1-fb',
                'reference' => 'reference-1-1234',
                'avatar' => 'Lorem ipsum dolor sit amet',
                'description' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'token' => 'token-1234',
                'token_secret' => 'Lorem ipsum dolor sit amet',
                'token_expires' => '2015-05-22 21:52:44',
                'active' => false,
                'data' => '',
                'created' => '2015-05-22 21:52:44',
                'modified' => '2015-05-22 21:52:44',
            ]),
        ];
        $actual = $this->User->socialConnectLinkList($socialAccounts);
        $expected = '<a class="btn btn-social btn-facebook disabled"><span class="fa fa-facebook"></span> Connected with Facebook</a>';
        $expected .= '<a href="/link-social/google" class="btn btn-social btn-google"><span class="fa fa-google"></span> Connect with Google</a>';
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test social connect link list, social is not enabled
     *
     * @return void
     */
    public function testSocialConnectLinkListSocialIsNotEnabled()
    {
        Configure::write('Users.Social.login', false);

        Configure::write('OAuth.providers.facebook.options.clientId', 'testclientidtestclientid');
        Configure::write('OAuth.providers.facebook.options.clientSecret', 'testclientsecrettestclientsecret');

        Configure::write('OAuth.providers.google.options.clientId', 'testclientidgoogtestclientidgoog');
        Configure::write('OAuth.providers.google.options.clientSecret', 'testclientsecretgoogtestclientsecretgoog');

        $socialAccounts = [
            new SocialAccount([
                'id' => '00000000-0000-0000-0000-000000000001',
                'user_id' => '00000000-0000-0000-0000-000000000001',
                'provider' => 'Facebook',
                'username' => 'user-1-fb',
                'reference' => 'reference-1-1234',
                'avatar' => 'Lorem ipsum dolor sit amet',
                'description' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'token' => 'token-1234',
                'token_secret' => 'Lorem ipsum dolor sit amet',
                'token_expires' => '2015-05-22 21:52:44',
                'active' => false,
                'data' => '',
                'created' => '2015-05-22 21:52:44',
                'modified' => '2015-05-22 21:52:44',
            ]),
        ];
        $actual = $this->User->socialConnectLinkList($socialAccounts);
        $expected = '';
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test social connect link list, social is enabled but any provider was configured
     *
     * @return void
     */
    public function testSocialConnectLinkListSocialEnabledButNotConfiguredProvider()
    {
        Configure::write('Users.Social.login', true);

        $socialAccounts = [
            new SocialAccount([
                'id' => '00000000-0000-0000-0000-000000000001',
                'user_id' => '00000000-0000-0000-0000-000000000001',
                'provider' => 'Facebook',
                'username' => 'user-1-fb',
                'reference' => 'reference-1-1234',
                'avatar' => 'Lorem ipsum dolor sit amet',
                'description' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'token' => 'token-1234',
                'token_secret' => 'Lorem ipsum dolor sit amet',
                'token_expires' => '2015-05-22 21:52:44',
                'active' => false,
                'data' => '',
                'created' => '2015-05-22 21:52:44',
                'modified' => '2015-05-22 21:52:44',
            ]),
        ];
        $actual = $this->User->socialConnectLinkList($socialAccounts);
        $expected = '';
        $this->assertEquals($expected, $actual);
    }
}

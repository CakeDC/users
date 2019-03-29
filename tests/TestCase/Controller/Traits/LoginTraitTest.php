<?php
/**
 * Copyright 2010 - 2019, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2018, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Test\TestCase\Controller\Traits;

use Authentication\Authenticator\Result;
use Authentication\Authenticator\SessionAuthenticator;
use Authentication\Identifier\IdentifierCollection;
use CakeDC\Auth\Authentication\Failure;
use CakeDC\Auth\Authenticator\FormAuthenticator;
use CakeDC\Users\Authenticator\SocialAuthenticator;
use CakeDC\Users\Controller\Component\LoginComponent;
use Cake\Controller\ComponentRegistry;
use Cake\Event\Event;
use Cake\Http\Response;
use Cake\Http\ServerRequest;

class LoginTraitTest extends BaseTraitTest
{
    /**
     * setup
     *
     * @return void
     */
    public function setUp()
    {
        $this->traitClassName = 'CakeDC\Users\Controller\Traits\LoginTrait';
        $this->traitMockMethods = ['dispatchEvent', 'isStopped', 'redirect', 'getUsersTable', 'set'];

        parent::setUp();
        $request = new ServerRequest();
        $this->Trait = $this->getMockBuilder('CakeDC\Users\Controller\Traits\LoginTrait')
            ->setMethods(['dispatchEvent', 'redirect', 'set', 'loadComponent', 'getRequest'])
            ->getMockForTrait();

        $this->Trait->Auth = $this->getMockBuilder('Cake\Controller\Component\AuthComponent')
            ->setMethods(['setConfig'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->Trait->request = $request;
    }

    /**
     * tearDown
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * test
     *
     * @return void
     */
    public function testLoginHappy()
    {
        $identifiers = new IdentifierCollection();
        $SessionAuth = new SessionAuthenticator($identifiers);

        $sessionFailure = new Failure(
            $SessionAuth,
            new Result(null, Result::FAILURE_IDENTITY_NOT_FOUND)
        );
        $failures = [$sessionFailure];

        $this->_mockDispatchEvent(new Event('event'));
        $this->Trait->request = $this->getMockBuilder('Cake\Http\ServerRequest')
            ->setMethods(['is'])
            ->getMock();
        $this->Trait->request->expects($this->any())
            ->method('is')
            ->with('post')
            ->will($this->returnValue(true));

        $this->_mockFlash();
        $this->_mockAuthentication(['id' => 1], $failures);
        $this->Trait->Flash->expects($this->never())
            ->method('error');
        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with($this->successLoginRedirect)
            ->will($this->returnValue(new Response()));
        $this->Trait->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($this->Trait->request));

        $registry = new ComponentRegistry();
        $config = [
            'component' => 'CakeDC/Users.Login',
            'defaultMessage' => __d('cake_d_c/users', 'Username or password is incorrect'),
            'messages' => [
                FormAuthenticator::FAILURE_INVALID_RECAPTCHA => __d('cake_d_c/users', 'Invalid reCaptcha')
            ],
            'targetAuthenticator' => FormAuthenticator::class
        ];
        $Login = $this->getMockBuilder(LoginComponent::class)
            ->setMethods(['getController'])
            ->setConstructorArgs([$registry, $config])
            ->getMock();

        $Login->expects($this->any())
            ->method('getController')
            ->will($this->returnValue($this->Trait));
        $this->Trait->expects($this->any())
            ->method('loadComponent')
            ->with(
                $this->equalTo('CakeDC/Users.Login'),
                $this->equalTo($config)
            )
            ->will($this->returnValue($Login));

        $result = $this->Trait->login();
        $this->assertInstanceOf(Response::class, $result);
    }

    /**
     * test
     *
     * @return void
     */
    public function testLoginRehash()
    {
        $this->_mockDispatchEvent(new Event('event'));
        $this->Trait->request = $this->getMockBuilder('Cake\Network\Request')
            ->setMethods(['is'])
            ->getMock();
        $this->Trait->request->expects($this->any())
            ->method('is')
            ->with('post')
            ->will($this->returnValue(true));
        $authenticate = $this->getMockBuilder('Cake\Auth\FormAuthenticate')
            ->setMethods(['needsPasswordRehash'])
            ->disableOriginalConstructor()
            ->getMock();
        $authenticate->expects($this->any())
            ->method('needsPasswordRehash')
            ->will($this->returnValue(true));
        $this->Trait->Auth = $this->getMockBuilder('Cake\Controller\Component\AuthComponent')
            ->setMethods(['user', 'identify', 'setUser', 'redirectUrl', 'authenticationProvider'])
            ->disableOriginalConstructor()
            ->getMock();
        $user = [
            'id' => '00000000-0000-0000-0000-000000000001',
        ];
        $redirectLoginOK = '/';
        $this->Trait->Auth->expects($this->at(0))
            ->method('identify')
            ->will($this->returnValue($user));
        $this->Trait->Auth->expects($this->atMost(2))
            ->method('authenticationProvider')
            ->will($this->returnValue($authenticate));
        $this->Trait->Auth->expects($this->at(3))
            ->method('setUser')
            ->with($user);
        $this->Trait->Auth->expects($this->at(4))
            ->method('redirectUrl')
            ->will($this->returnValue($redirectLoginOK));
        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with($redirectLoginOK);
        $this->Trait->GoogleAuthenticator = $this->getMockBuilder(GoogleAuthenticatorComponent::class)
            ->disableOriginalConstructor()
            ->setMethods(['createSecret', 'getQRCodeImageAsDataUri'])
            ->getMock();
        $this->Trait->login();
    }

    /**
     * test
     *
     * @return void
     */
    public function testLoginHappyReset()
    {
        $identifiers = new IdentifierCollection();
        $SessionAuth = new SessionAuthenticator($identifiers);

        $sessionFailure = new Failure(
            $SessionAuth,
            new Result(null, Result::FAILURE_IDENTITY_NOT_FOUND)
        );
        $failures = [$sessionFailure];

        $this->_mockDispatchEvent(new Event('event'));
        $this->Trait->request = $this->getMockBuilder('Cake\Http\ServerRequest')
            ->setMethods(['is'])
            ->getMock();
        $this->Trait->request->expects($this->any())
            ->method('is')
            ->with('post')
            ->will($this->returnValue(true));

        $this->_mockFlash();
        $this->_mockAuthenticationWithPasswordRehash(['id' => '00000000-0000-0000-0000-000000000001',
            'username' => 'user-1',
            'email' => 'user-1@test.com',
            'password' => '12345',
            'first_name' => 'first1',
            'last_name' => 'last1',
            'token' => 'ae93ddbe32664ce7927cf0c5c5a5e59d',
            'token_expires' => '2035-06-24 17:33:54',
            'api_token' => 'yyy',
            'activation_date' => '2015-06-24 17:33:54',
            'secret' => 'yyy',
            'secret_verified' => false,
            'tos_date' => '2015-06-24 17:33:54',
            'active' => false,
            'is_superuser' => true,
            'role' => 'admin',
            'created' => '2015-06-24 17:33:54',
            'modified' => '2015-06-24 17:33:54'], $failures);
        $this->Trait->Flash->expects($this->never())
            ->method('error');
        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with($this->successLoginRedirect)
            ->will($this->returnValue(new Response()));
        $this->Trait->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($this->Trait->request));

        $registry = new ComponentRegistry();
        $config = [
            'component' => 'CakeDC/Users.Login',
            'defaultMessage' => __d('cake_d_c/users', 'Username or password is incorrect'),
            'messages' => [
                FormAuthenticator::FAILURE_INVALID_RECAPTCHA => __d('cake_d_c/users', 'Invalid reCaptcha')
            ],
            'targetAuthenticator' => FormAuthenticator::class
        ];
        $Login = $this->getMockBuilder(LoginComponent::class)
            ->setMethods(['getController'])
            ->setConstructorArgs([$registry, $config])
            ->getMock();

        $Login->expects($this->any())
            ->method('getController')
            ->will($this->returnValue($this->Trait));
        $this->Trait->expects($this->any())
            ->method('loadComponent')
            ->with(
                $this->equalTo('CakeDC/Users.Login'),
                $this->equalTo($config)
            )
            ->will($this->returnValue($Login));

        $result = $this->Trait->login();
        $this->assertInstanceOf(Response::class, $result);
    }

    /**
     * test
     *
     * @return void
     */
    public function testLoginGet()
    {
        $this->_mockDispatchEvent(new Event('event'));
        $this->Trait->request = $this->getMockBuilder('Cake\Http\ServerRequest')
            ->setMethods(['is'])
            ->getMock();
        $this->Trait->request->expects($this->once())
            ->method('is')
            ->with('post')
            ->will($this->returnValue(false));
        $this->Trait->Flash = $this->getMockBuilder('Cake\Controller\Component\FlashComponent')
            ->setMethods(['error'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->Trait->Flash->expects($this->never())
            ->method('error');

        $this->Trait->expects($this->never())
            ->method('redirect');

        $this->_mockAuthentication();

        $registry = new ComponentRegistry();
        $config = [
            'component' => 'CakeDC/Users.Login',
            'defaultMessage' => __d('cake_d_c/users', 'Username or password is incorrect'),
            'messages' => [
                FormAuthenticator::FAILURE_INVALID_RECAPTCHA => __d('cake_d_c/users', 'Invalid reCaptcha')
            ],
            'targetAuthenticator' => FormAuthenticator::class
        ];
        $Login = $this->getMockBuilder(LoginComponent::class)
            ->setMethods(['getController'])
            ->setConstructorArgs([$registry, $config])
            ->getMock();

        $Login->expects($this->any())
            ->method('getController')
            ->will($this->returnValue($this->Trait));
        $this->Trait->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($this->Trait->request));
        $this->Trait->expects($this->any())
            ->method('loadComponent')
            ->with(
                $this->equalTo('CakeDC/Users.Login'),
                $this->equalTo($config)
            )
            ->will($this->returnValue($Login));

        $this->Trait->login();
    }

    /**
     * test
     *
     * @return void
     */
    public function testLogout()
    {
        $this->_mockDispatchEvent(new Event('event'));
        $this->Trait->Auth = $this->getMockBuilder('Cake\Controller\Component\AuthComponent')
            ->setMethods(['logout', 'user'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->_mockAuthentication([
            'id' => 1
        ]);
        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with($this->logoutRedirect);
        $this->Trait->Flash = $this->getMockBuilder('Cake\Controller\Component\FlashComponent')
            ->setMethods(['success'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->Trait->Flash->expects($this->once())
            ->method('success')
            ->with('You\'ve successfully logged out');
        $this->Trait->logout();
    }

    /**
     * Data provider for testLogin
     */
    public function dataProviderLogin()
    {
        $socialLoginConfig = [
            'component' => 'CakeDC/Users.Login',
            'defaultMessage' => __d('cake_d_c/users', 'Could not proceed with social account. Please try again'),
            'messages' => [
                SocialAuthenticator::FAILURE_USER_NOT_ACTIVE => __d(
                    'cake_d_c/users',
                    'Your user has not been validated yet. Please check your inbox for instructions'
                ),
                SocialAuthenticator::FAILURE_ACCOUNT_NOT_ACTIVE => __d(
                    'cake_d_c/users',
                    'Your social account has not been validated yet. Please check your inbox for instructions'
                )
            ],
            'targetAuthenticator' => SocialAuthenticator::class
        ];
        $loginConfig = [
            'component' => 'CakeDC/Users.Login',
            'defaultMessage' => __d('cake_d_c/users', 'Username or password is incorrect'),
            'messages' => [
                FormAuthenticator::FAILURE_INVALID_RECAPTCHA => __d('cake_d_c/users', 'Invalid reCaptcha'),
            ],
            'targetAuthenticator' => FormAuthenticator::class
        ];

        return [
            [
                SocialAuthenticator::class,
                SocialAuthenticator::FAILURE_USER_NOT_ACTIVE,
                'Your user has not been validated yet. Please check your inbox for instructions',
                'socialLogin',
                $socialLoginConfig
            ],
            [
                SocialAuthenticator::class,
                SocialAuthenticator::FAILURE_ACCOUNT_NOT_ACTIVE,
                'Your social account has not been validated yet. Please check your inbox for instructions',
                'socialLogin',
                $socialLoginConfig
            ],
            [
                SocialAuthenticator::class,
                Result::FAILURE_IDENTITY_NOT_FOUND,
                'Could not proceed with social account. Please try again',
                'socialLogin',
                $socialLoginConfig
            ],
            [
                FormAuthenticator::class,
                Result::FAILURE_IDENTITY_NOT_FOUND,
                'Username or password is incorrect',
                'login',
                $loginConfig
            ],
            [
                FormAuthenticator::class,
                FormAuthenticator::FAILURE_INVALID_RECAPTCHA,
                'Invalid reCaptcha',
                'login',
                $loginConfig
            ]
        ];
    }
    /**
     * test socialLogin/login failure
     *
     * @dataProvider dataProviderLogin
     * @return void
     */
    public function testLogin($AuthClass, $resultStatus, $message, $method, $failureConfig)
    {
        $identifiers = new IdentifierCollection([
            'CakeDC/Users.Social'
        ]);
        $FormAuth = new FormAuthenticator($identifiers);
        $SessionAuth = new SessionAuthenticator($identifiers);
        $SocialAuth = new $AuthClass($identifiers);

        $sessionFailure = new Failure(
            $SessionAuth,
            new Result(null, Result::FAILURE_IDENTITY_NOT_FOUND)
        );
        $formFailure = new Failure(
            $FormAuth,
            new Result(null, $resultStatus, [
                'Password' => []
            ])
        );
        $socialFailure = new Failure(
            $SocialAuth,
            new Result(null, $resultStatus)
        );
        $failures = [$sessionFailure, $formFailure, $socialFailure];

        $this->_mockDispatchEvent(new Event('event'));
        $this->Trait->request = $this->getMockBuilder('Cake\Http\ServerRequest')
            ->setMethods(['is'])
            ->getMock();
        $this->Trait->request->expects($this->any())
            ->method('is')
            ->with('post')
            ->will($this->returnValue(true));
        $this->_mockFlash();
        $this->_mockAuthentication(null, $failures);
        $this->Trait->Flash->expects($this->once())
            ->method('error')
            ->with($message);
        $this->Trait->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($this->Trait->request));

        $registry = new ComponentRegistry();
        $Login = $this->getMockBuilder(LoginComponent::class)
            ->setMethods(['getController'])
            ->setConstructorArgs([$registry, $failureConfig])
            ->getMock();

        $Login->expects($this->any())
            ->method('getController')
            ->will($this->returnValue($this->Trait));
        $this->Trait->expects($this->any())
            ->method('loadComponent')
            ->with(
                $this->equalTo('CakeDC/Users.Login'),
                $this->equalTo($failureConfig)
            )
            ->will($this->returnValue($Login));

        if ($method === 'login') {
            $this->Trait->expects($this->never())
                ->method('redirect');
            $result = $this->Trait->$method();
            $this->assertNull($result);
        } else {
            $this->Trait->expects($this->once())
                ->method('redirect')
                ->with(['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'login'])
                ->will($this->returnValue(new Response()));
            $result = $this->Trait->$method();
            $this->assertInstanceOf(Response::class, $result);
        }
    }

    /**
     * test socialLogin success
     *
     * @return void
     */
    public function testSocialLoginSuccess()
    {
        $identifiers = new IdentifierCollection([
            'CakeDC/Users.Social'
        ]);
        $FormAuth = new FormAuthenticator($identifiers);
        $SessionAuth = new SessionAuthenticator($identifiers);

        $sessionFailure = new Failure(
            $SessionAuth,
            new Result(null, Result::FAILURE_IDENTITY_NOT_FOUND)
        );
        $formFailure = new Failure(
            $FormAuth,
            new Result(null, Result::FAILURE_CREDENTIALS_MISSING, [
                'Password' => []
            ])
        );
        $failures = [$sessionFailure, $formFailure];

        $this->_mockDispatchEvent(new Event('event'));
        $this->Trait->request = $this->getMockBuilder('Cake\Http\ServerRequest')
            ->setMethods(['is'])
            ->getMock();
        $this->Trait->request->expects($this->any())
            ->method('is')
            ->with('post')
            ->will($this->returnValue(true));

        $this->_mockFlash();
        $this->_mockAuthentication(['id' => 1], $failures);
        $this->Trait->Flash->expects($this->never())
            ->method('error');
        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with($this->successLoginRedirect)
            ->will($this->returnValue(new Response()));
        $this->Trait->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($this->Trait->request));

        $registry = new ComponentRegistry();
        $config = [
            'component' => 'CakeDC/Users.Login',
            'defaultMessage' => __d('cake_d_c/users', 'Could not proceed with social account. Please try again'),
            'messages' => [
                SocialAuthenticator::FAILURE_USER_NOT_ACTIVE => __d(
                    'cake_d_c/users',
                    'Your user has not been validated yet. Please check your inbox for instructions'
                ),
                SocialAuthenticator::FAILURE_ACCOUNT_NOT_ACTIVE => __d(
                    'cake_d_c/users',
                    'Your social account has not been validated yet. Please check your inbox for instructions'
                )
            ],
            'targetAuthenticator' => SocialAuthenticator::class
        ];
        $Login = $this->getMockBuilder(LoginComponent::class)
            ->setMethods(['getController'])
            ->setConstructorArgs([$registry, $config])
            ->getMock();

        $Login->expects($this->any())
            ->method('getController')
            ->will($this->returnValue($this->Trait));
        $this->Trait->expects($this->any())
            ->method('loadComponent')
            ->with(
                $this->equalTo('CakeDC/Users.Login'),
                $this->equalTo($config)
            )
            ->will($this->returnValue($Login));

        $result = $this->Trait->socialLogin();
        $this->assertInstanceOf(Response::class, $result);
    }
}

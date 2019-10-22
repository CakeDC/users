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

namespace CakeDC\Users\Test\TestCase\Controller\Traits;

use Authentication\Authenticator\Result;
use Authentication\Authenticator\SessionAuthenticator;
use Authentication\Identifier\IdentifierCollection;
use Authentication\Identifier\PasswordIdentifier;
use Cake\Auth\DefaultPasswordHasher;
use Cake\Controller\ComponentRegistry;
use Cake\Event\Event;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use CakeDC\Auth\Authentication\Failure;
use CakeDC\Auth\Authenticator\FormAuthenticator;
use CakeDC\Users\Authenticator\SocialAuthenticator;
use CakeDC\Users\Controller\Component\LoginComponent;

class LoginTraitTest extends BaseTraitTest
{
    /**
     * setup
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->traitClassName = 'CakeDC\Users\Controller\UsersController';
        $this->traitMockMethods = ['dispatchEvent', 'isStopped', 'redirect', 'getUsersTable', 'set'];

        parent::setUp();
        $this->Trait->setRequest(new ServerRequest());
        $this->Trait = $this->getMockBuilder('CakeDC\Users\Controller\UsersController')
            ->setMethods(['dispatchEvent', 'redirect', 'set', 'loadComponent'])
            ->getMock();

        $this->Trait->Auth = $this->getMockBuilder('Cake\Controller\Component\AuthComponent')
            ->setMethods(['setConfig'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * tearDown
     *
     * @return void
     */
    public function tearDown(): void
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

        $request = $this->getMockBuilder('Cake\Http\ServerRequest')
            ->setMethods(['is'])
            ->getMock();

        $this->_mockRequestPost();
        $this->Trait->getRequest()->expects($this->never())
            ->method('getData');
        $this->Trait->setRequest($request);

        $this->_mockFlash();
        $user = $this->Trait->getUsersTable()->get('00000000-0000-0000-0000-000000000002');
        $passwordBefore = $user['password'];
        $this->assertNotEmpty($passwordBefore);
        $this->_mockAuthentication($user->toArray(), $failures);
        $this->Trait->Flash->expects($this->never())
            ->method('error');
        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with($this->successLoginRedirect)
            ->will($this->returnValue(new Response()));

        $registry = new ComponentRegistry();
        $config = [
            'component' => 'CakeDC/Users.Login',
            'defaultMessage' => __d('cake_d_c/users', 'Username or password is incorrect'),
            'messages' => [
                FormAuthenticator::FAILURE_INVALID_RECAPTCHA => __d('cake_d_c/users', 'Invalid reCaptcha'),
            ],
            'targetAuthenticator' => FormAuthenticator::class,
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
        $userAfter = $this->Trait->getUsersTable()->get('00000000-0000-0000-0000-000000000002');
        $passwordAfter = $userAfter['password'];
        $this->assertSame($passwordBefore, $passwordAfter);
    }

    /**
     * test
     *
     * @return void
     */
    public function testLoginRehash()
    {
        $passwordIdentifier = $this->getMockBuilder(PasswordIdentifier::class)
            ->setMethods(['needsPasswordRehash'])
            ->getMock();
        $passwordIdentifier->expects($this->once())
            ->method('needsPasswordRehash')
            ->willReturn(true);
        $identifiers = new IdentifierCollection([]);
        $identifiers->set('Password', $passwordIdentifier);

        $SessionAuth = new SessionAuthenticator($identifiers);

        $sessionFailure = new Failure(
            $SessionAuth,
            new Result(null, Result::FAILURE_IDENTITY_NOT_FOUND)
        );
        $failures = [$sessionFailure];

        $userPassword = 'testLoginRehash' . time();
        $this->_mockDispatchEvent(new Event('event'));
        $this->_mockRequestPost();
        $this->Trait->getRequest()->expects($this->once())
            ->method('getData')
            ->with($this->equalTo('password'))
            ->willReturn($userPassword);

        $this->_mockFlash();
        $user = $this->Trait->getUsersTable()->get('00000000-0000-0000-0000-000000000002');
        $passwordBefore = $user['password'];
        $this->assertNotEmpty($passwordBefore);
        $this->_mockAuthentication($user->toArray(), $failures, $identifiers);
        $this->Trait->Flash->expects($this->never())
            ->method('error');
        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with($this->successLoginRedirect)
            ->will($this->returnValue(new Response()));

        $registry = new ComponentRegistry();
        $config = [
            'component' => 'CakeDC/Users.Login',
            'defaultMessage' => __d('cake_d_c/users', 'Username or password is incorrect'),
            'messages' => [
                FormAuthenticator::FAILURE_INVALID_RECAPTCHA => __d('cake_d_c/users', 'Invalid reCaptcha'),
            ],
            'targetAuthenticator' => FormAuthenticator::class,
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
        $userAfter = $this->Trait->getUsersTable()->get('00000000-0000-0000-0000-000000000002');
        $passwordAfter = $userAfter['password'];
        $this->assertNotEquals($passwordBefore, $passwordAfter);
        $passwordHasher = new DefaultPasswordHasher();
        $check = $passwordHasher->check($userPassword, $passwordAfter);
        $this->assertTrue($check);
    }

    /**
     * test
     *
     * @return void
     */
    public function testLoginGet()
    {
        $this->_mockDispatchEvent(new Event('event'));
        $request = $this->getMockBuilder('Cake\Http\ServerRequest')
            ->setMethods(['is'])
            ->getMock();
        $this->Trait->setRequest($request);
        $request->expects($this->once())
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
                FormAuthenticator::FAILURE_INVALID_RECAPTCHA => __d('cake_d_c/users', 'Invalid reCaptcha'),
            ],
            'targetAuthenticator' => FormAuthenticator::class,
        ];
        $Login = $this->getMockBuilder(LoginComponent::class)
            ->setMethods(['getController'])
            ->setConstructorArgs([$registry, $config])
            ->getMock();

        $Login->expects($this->any())
            ->method('getController')
            ->will($this->returnValue($this->Trait));
        // $this->Trait->expects($this->any())
            // ->method('getRequest')
            // ->will($this->returnValue($request));
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
            'id' => 1,
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
                ),
            ],
            'targetAuthenticator' => SocialAuthenticator::class,
        ];
        $loginConfig = [
            'component' => 'CakeDC/Users.Login',
            'defaultMessage' => __d('cake_d_c/users', 'Username or password is incorrect'),
            'messages' => [
                FormAuthenticator::FAILURE_INVALID_RECAPTCHA => __d('cake_d_c/users', 'Invalid reCaptcha'),
            ],
            'targetAuthenticator' => FormAuthenticator::class,
        ];

        return [
            [
                SocialAuthenticator::class,
                SocialAuthenticator::FAILURE_USER_NOT_ACTIVE,
                'Your user has not been validated yet. Please check your inbox for instructions',
                'socialLogin',
                $socialLoginConfig,
            ],
            [
                SocialAuthenticator::class,
                SocialAuthenticator::FAILURE_ACCOUNT_NOT_ACTIVE,
                'Your social account has not been validated yet. Please check your inbox for instructions',
                'socialLogin',
                $socialLoginConfig,
            ],
            [
                SocialAuthenticator::class,
                Result::FAILURE_IDENTITY_NOT_FOUND,
                'Could not proceed with social account. Please try again',
                'socialLogin',
                $socialLoginConfig,
            ],
            [
                FormAuthenticator::class,
                Result::FAILURE_IDENTITY_NOT_FOUND,
                'Username or password is incorrect',
                'login',
                $loginConfig,
            ],
            [
                FormAuthenticator::class,
                FormAuthenticator::FAILURE_INVALID_RECAPTCHA,
                'Invalid reCaptcha',
                'login',
                $loginConfig,
            ],
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
            'CakeDC/Users.Social',
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
                'Password' => [],
            ])
        );
        $socialFailure = new Failure(
            $SocialAuth,
            new Result(null, $resultStatus)
        );
        $failures = [$sessionFailure, $formFailure, $socialFailure];

        $this->_mockDispatchEvent(new Event('event'));

        $request = $this->getMockBuilder('Cake\Http\ServerRequest')
            ->setMethods(['is'])
            ->getMock();
        $request->expects($this->any())
            ->method('is')
            ->with('post')
            ->will($this->returnValue(true));
        $this->Trait->setRequest($request);

        $this->_mockFlash();
        $this->_mockAuthentication(null, $failures);
        $this->Trait->Flash->expects($this->once())
            ->method('error')
            ->with($message);

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
                ->with(['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'login', 'prefix' => false])
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
            'CakeDC/Users.Social',
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
                'Password' => [],
            ])
        );
        $failures = [$sessionFailure, $formFailure];

        $this->_mockDispatchEvent(new Event('event'));

        $request = $this->getMockBuilder('Cake\Http\ServerRequest')
            ->setMethods(['is'])
            ->getMock();
        $request->expects($this->any())
            ->method('is')
            ->with('post')
            ->will($this->returnValue(true));
        $this->Trait->setRequest($request);

        $this->_mockFlash();
        $this->_mockAuthentication(['id' => 1], $failures);
        $this->Trait->Flash->expects($this->never())
            ->method('error');
        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with($this->successLoginRedirect)
            ->will($this->returnValue(new Response()));

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
                ),
            ],
            'targetAuthenticator' => SocialAuthenticator::class,
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

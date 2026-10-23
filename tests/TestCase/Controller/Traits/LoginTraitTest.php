<?php
declare(strict_types=1);

/**
 * Copyright 2010 - 2026, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2026, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Test\TestCase\Controller\Traits;

use Authentication\Authenticator\AuthenticatorCollection;
use Authentication\Authenticator\Result;
use Authentication\Authenticator\SessionAuthenticator;
use Authentication\Identifier\PasswordIdentifier;
use Authentication\Identity;
use Authentication\PasswordHasher\DefaultPasswordHasher;
use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\I18n\DateTime;
use CakeDC\Auth\Authentication\AuthenticationService;
use CakeDC\Auth\Authentication\Failure;
use CakeDC\Auth\Authenticator\FormAuthenticator;
use CakeDC\Users\Authenticator\SocialAuthenticator;
use CakeDC\Users\Controller\Component\LoginComponent;
use PHPUnit\Framework\Attributes\DataProvider;

class LoginTraitTest extends BaseTrait
{
    /**
     * setup
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->traitClassName = 'CakeDC\Users\Controller\UsersController';
        $this->traitMockMethods = ['dispatchEvent', 'redirect', 'getUsersTable', 'set', 'loadComponent'];
        $this->traitMockAddMethods = ['isStopped'];

        parent::setUp();
        $this->Trait->setRequest(new ServerRequest());

        // $this->Trait->Auth = $this->getMockBuilder('Cake\Controller\Component\AuthComponent')
        // ->onlyMethods(['setConfig'])
        // ->disableOriginalConstructor()
        // ->getMock();
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
    public function testLoginHappy(): void
    {
        $identifier = new PasswordIdentifier();
        $SessionAuth = new SessionAuthenticator($identifier);

        $sessionFailure = new Failure(
            $SessionAuth,
            new Result(null, Result::FAILURE_IDENTITY_NOT_FOUND),
        );
        $failures = [$sessionFailure];

        $this->mockDispatchEvent(new Event('event'));

        $request = $this->getMockBuilder('Cake\Http\ServerRequest')
            ->onlyMethods(['is', 'getData'])
            ->getMock();
        $request->expects($this->never())
            ->method('is');
        $request->expects($this->never())
            ->method('getData');
        $this->Trait->setRequest($request);

        $flash = $this->mockFlash();
        $user = $this->Trait->getUsersTable()->get('00000000-0000-0000-0000-000000000002');
        $passwordBefore = $user['password'];
        $this->assertNotEmpty($passwordBefore);
        $lastLoginBefore = $user['last_login'];
        $this->assertNotEmpty($lastLoginBefore);
        $this->mockAuthentication($user->toArray(), $failures);
        $flash->expects('set')->never();
        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with($this->successLoginRedirect)
            ->willReturn(new Response());

        $registry = new ComponentRegistry(new Controller(new ServerRequest()));
        $config = [
            'component' => 'CakeDC/Users.Login',
            'defaultMessage' => __d('cake_d_c/users', 'Username or password is incorrect'),
            'messages' => [
                FormAuthenticator::FAILURE_INVALID_RECAPTCHA => __d('cake_d_c/users', 'Invalid reCaptcha'),
            ],
            'targetAuthenticator' => FormAuthenticator::class,
        ];
        $Login = $this->getMockBuilder(LoginComponent::class)
            ->onlyMethods(['getController'])
            ->setConstructorArgs([$registry, $config])
            ->getMock();

        $Login->expects($this->atLeastOnce())
            ->method('getController')
            ->willReturn($this->Trait);
        $this->Trait->expects($this->atLeastOnce())
            ->method('loadComponent')
            ->with(
                $this->equalTo('CakeDC/Users.Login'),
                $this->equalTo($config),
            )
            ->willReturn($Login);

        $result = $this->Trait->login();
        $this->assertInstanceOf(Response::class, $result);
        $userAfter = $this->Trait->getUsersTable()->get('00000000-0000-0000-0000-000000000002');
        $passwordAfter = $userAfter['password'];
        $this->assertSame($passwordBefore, $passwordAfter);
        $lastLoginAfter = $userAfter['last_login'];
        $this->assertNotEmpty($lastLoginAfter);
        $now = DateTime::now();
        $this->assertEqualsWithDelta($lastLoginAfter->timestamp, $now->timestamp, 2);
    }

    /**
     * test
     *
     * @return void
     */
    public function testLoginRehash(): void
    {
        Configure::write('Auth.PasswordRehash', [
            'authenticators' => [
                'Form' => 'Password',
            ],
        ]);

        $passwordIdentifier = $this->getMockBuilder(PasswordIdentifier::class)
            ->onlyMethods(['needsPasswordRehash'])
            ->getMock();
        $passwordIdentifier->expects($this->atLeastOnce())
            ->method('needsPasswordRehash')
            ->willReturn(true);

        $formAuthenticator = new FormAuthenticator($passwordIdentifier);

        $authenticatorCollection = $this->getMockBuilder(AuthenticatorCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $authenticatorCollection->expects($this->atLeastOnce())
            ->method('has')->with('Form')->willReturn(true);
        $authenticatorCollection->expects($this->atLeastOnce())
            ->method('get')->with('Form')->willReturn($formAuthenticator);

        $user = $this->Trait->getUsersTable()->get('00000000-0000-0000-0000-000000000002');
        $passwordBefore = $user['password'];
        $userPassword = 'testLoginRehash' . time();

        $service = $this->getMockBuilder(AuthenticationService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $service->expects($this->atLeastOnce())
            ->method('authenticators')
            ->willReturn($authenticatorCollection);
        $service->expects($this->atLeastOnce())
            ->method('getAuthenticationProvider')
            ->willReturn($formAuthenticator);

        $result = new Result($user, Result::SUCCESS);
        $service->expects($this->atLeastOnce())->method('getResult')->willReturn($result);

        $this->mockDispatchEvent(new Event('event'));

        $request = $this->getMockBuilder('Cake\Http\ServerRequest')
            ->onlyMethods(['is', 'getData'])
            ->getMock();

        $request->expects($this->never())
            ->method('is')
            ->with('post');

        $request->expects($this->atLeastOnce())
            ->method('getData')
            ->willReturnCallback(function ($key = null) use ($userPassword, $user) {
                if ($key === 'password') {
                    return $userPassword;
                }
                if ($key === 'username' || $key === 'email') {
                    return $user->email;
                }

                return [];
            });

        $identityWrapper = new Identity($user);
        $request = $request->withAttribute('authentication', $service);
        $request = $request->withAttribute('identity', $identityWrapper);

        $this->Trait->setRequest($request);

        $authComponent = $this->getMockBuilder(Component::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getConfig'])
            ->getMock();
        $authComponent->expects($this->atLeastOnce())
            ->method('getConfig')
            ->with('loginRedirect')
            ->willReturn('/home');

        $this->Trait->components()->set('Authentication', $authComponent);

        $flash = $this->mockFlash();
        $flash->expects('set')->never();

        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with($this->successLoginRedirect)
            ->willReturn(new Response());

        $registry = new ComponentRegistry(new Controller(new ServerRequest()));
        $config = [
            'component' => 'CakeDC/Users.Login',
            'targetAuthenticator' => FormAuthenticator::class,
            'PasswordRehash' => [
                'authenticators' => ['Form' => 'Password'],
            ],
        ];

        $Login = $this->getMockBuilder(LoginComponent::class)
            ->onlyMethods(['getController'])
            ->setConstructorArgs([$registry, $config])
            ->getMock();

        $Login->expects($this->atLeastOnce())
            ->method('getController')
            ->willReturn($this->Trait);
        $this->Trait->expects($this->atLeastOnce())
            ->method('loadComponent')
            ->with(
                $this->equalTo('CakeDC/Users.Login'),
                $this->anything(),
            )
            ->willReturn($Login);

        $result = $this->Trait->login();
        $this->assertInstanceOf(Response::class, $result);

        $userAfter = $this->Trait->getUsersTable()->get('00000000-0000-0000-0000-000000000002');
        $this->assertNotEquals($passwordBefore, $userAfter['password']);

        $passwordHasher = new DefaultPasswordHasher();
        $this->assertTrue($passwordHasher->check($userPassword, $userAfter['password']));
    }

    public function testLoginRehashWithAuthenticatorStructure(): void
    {
        Configure::write('Auth.PasswordRehash', [
            'authenticators' => [
                'Form' => 'Authentication.Password',
            ],
        ]);

        $userId = '00000000-0000-0000-0000-000000000002';
        $user = $this->Trait->getUsersTable()->get($userId);
        $oldHash = '$2y$10$OldHashNeedsUpgrade00000000000000000000000000000000';
        $this->Trait->getUsersTable()->updateAll(
            ['password' => $oldHash],
            ['id' => $userId],
        );

        $passwordIdentifier = $this->getMockBuilder(PasswordIdentifier::class)
            ->onlyMethods(['needsPasswordRehash'])
            ->getMock();
        $passwordIdentifier->expects($this->atLeastOnce())
            ->method('needsPasswordRehash')
            ->willReturn(true);

        $formAuthenticator = new FormAuthenticator($passwordIdentifier);

        $authenticatorCollection = $this->getMockBuilder(AuthenticatorCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $authenticatorCollection->expects($this->atLeastOnce())
            ->method('has')->with('Form')->willReturn(true);
        $authenticatorCollection->expects($this->atLeastOnce())
            ->method('get')->with('Form')->willReturn($formAuthenticator);

        $service = $this->getMockBuilder(AuthenticationService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $service->expects($this->atLeastOnce())
            ->method('authenticators')
            ->willReturn($authenticatorCollection);
        $service->expects($this->atLeastOnce())
            ->method('getAuthenticationProvider')
            ->willReturn($formAuthenticator);

        $result = new Result($user, Result::SUCCESS);
        $service->expects($this->atLeastOnce())->method('getResult')->willReturn($result);

        $this->mockDispatchEvent(new Event('event'));

        $request = $this->getMockBuilder('Cake\Http\ServerRequest')
            ->onlyMethods(['is', 'getData'])
            ->getMock();

        $request->expects($this->never())
            ->method('is')
            ->with('post');

        $request->expects($this->atLeastOnce())
            ->method('getData')
            ->willReturnCallback(function ($key = null) use ($user) {
                if ($key === 'password') {
                    return 'password123';
                }
                if ($key === 'username' || $key === 'email') {
                    return $user->email;
                }

                return [];
            });

        $identityWrapper = new Identity($user);

        $request = $request->withAttribute('authentication', $service);
        $request = $request->withAttribute('identity', $identityWrapper);

        $this->Trait->setRequest($request);

        $authComponent = $this->getMockBuilder(Component::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getConfig'])
            ->getMock();

        $authComponent->expects($this->atLeastOnce())
            ->method('getConfig')
            ->with('loginRedirect')
            ->willReturn('/');

        $this->Trait->components()->set('Authentication', $authComponent);

        $registry = new ComponentRegistry(new Controller(new ServerRequest()));
        $config = [
            'component' => 'CakeDC/Users.Login',
            'targetAuthenticator' => FormAuthenticator::class,
            'PasswordRehash' => [
                'authenticators' => ['Form' => 'Authentication.Password'],
            ],
        ];

        $Login = $this->getMockBuilder(LoginComponent::class)
            ->onlyMethods(['getController'])
            ->setConstructorArgs([$registry, $config])
            ->getMock();

        $Login->expects($this->atLeastOnce())
            ->method('getController')
            ->willReturn($this->Trait);

        $this->Trait->expects($this->atLeastOnce())
            ->method('loadComponent')
            ->with(
                $this->equalTo('CakeDC/Users.Login'),
                $this->anything(),
            )
            ->willReturn($Login);

        $this->Trait->expects($this->once())
            ->method('redirect')
            ->willReturn(new Response());

        $this->Trait->login();

        $userAfter = $this->Trait->getUsersTable()->get($userId);

        $this->assertNotEquals(
            $oldHash,
            $userAfter->password,
        );
    }

    /**
     * test
     *
     * @return void
     */
    public function testLoginGet(): void
    {
        $this->mockDispatchEvent(new Event('event'));
        $request = $this->getMockBuilder('Cake\Http\ServerRequest')
            ->onlyMethods(['is'])
            ->getMock();
        $this->Trait->setRequest($request);
        $request->expects($this->once())
            ->method('is')
            ->with('post')
            ->willReturn(false);
        $flash = $this->mockFlash();
        $flash->expects('set')->never();

        $this->Trait->expects($this->never())
            ->method('redirect');

        $this->mockAuthentication();

        $registry = new ComponentRegistry(new Controller(new ServerRequest()));
        $config = [
            'component' => 'CakeDC/Users.Login',
            'defaultMessage' => __d('cake_d_c/users', 'Username or password is incorrect'),
            'messages' => [
                FormAuthenticator::FAILURE_INVALID_RECAPTCHA => __d('cake_d_c/users', 'Invalid reCaptcha'),
            ],
            'targetAuthenticator' => FormAuthenticator::class,
        ];
        $Login = $this->getMockBuilder(LoginComponent::class)
            ->onlyMethods(['getController'])
            ->setConstructorArgs([$registry, $config])
            ->getMock();

        $Login->expects($this->atLeastOnce())
            ->method('getController')
            ->willReturn($this->Trait);
        // $this->Trait->expects($this->atLeastOnce())
        // ->method('getRequest')
        // ->willReturn($request);
        $this->Trait->expects($this->atLeastOnce())
            ->method('loadComponent')
            ->with(
                $this->equalTo('CakeDC/Users.Login'),
                $this->equalTo($config),
            )
            ->willReturn($Login);

        $this->Trait->login();
    }

    /**
     * test
     *
     * @return void
     */
    public function testLogout(): void
    {
        $this->mockDispatchEvent(new Event('event'));
        $this->mockAuthLoggedIn([
            'id' => 1,
        ]);
        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with($this->logoutRedirect);
        $flash = $this->mockFlash();
        $flash->expects('set')
            ->with('You\'ve successfully logged out', ['element' => 'success']);
        $this->Trait->logout();
    }

    /**
     * Data provider for testLogin
     */
    public static function dataProviderLogin()
    {
        $socialLoginConfig = [
            'component' => 'CakeDC/Users.Login',
            'defaultMessage' => __d('cake_d_c/users', 'Could not proceed with social account. Please try again'),
            'messages' => [
                SocialAuthenticator::FAILURE_USER_NOT_ACTIVE => __d(
                    'cake_d_c/users',
                    'Your user has not been validated yet. Please check your inbox for instructions',
                ),
                SocialAuthenticator::FAILURE_ACCOUNT_NOT_ACTIVE => __d(
                    'cake_d_c/users',
                    'Your social account has not been validated yet. Please check your inbox for instructions',
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
    #[DataProvider('dataProviderLogin')]
    public function testLogin(string $AuthClass, string $resultStatus, string $message, string $method, array $failureConfig): void
    {
        $identifier = new PasswordIdentifier();
        $FormAuth = new FormAuthenticator($identifier);
        $SessionAuth = new SessionAuthenticator($identifier);
        $SocialAuth = new $AuthClass($identifier);

        $sessionFailure = new Failure(
            $SessionAuth,
            new Result(null, Result::FAILURE_IDENTITY_NOT_FOUND),
        );
        $formFailure = new Failure(
            $FormAuth,
            new Result(null, $resultStatus, [
                'Password' => [],
            ]),
        );
        $socialFailure = new Failure(
            $SocialAuth,
            new Result(null, $resultStatus),
        );
        $failures = [$sessionFailure, $formFailure, $socialFailure];

        $this->mockDispatchEvent(new Event('event'));

        $request = $this->getMockBuilder('Cake\Http\ServerRequest')
            ->onlyMethods(['is'])
            ->getMock();
        $request->expects($this->atLeastOnce())
            ->method('is')
            ->with('post')
            ->willReturn(true);
        $this->Trait->setRequest($request);

        $flash = $this->mockFlash();
        $this->mockAuthentication(null, $failures);
        $flash->expects('set')
            ->with($message, ['element' => 'error', 'key' => 'auth']);

        $registry = new ComponentRegistry(new Controller(new ServerRequest()));
        $Login = $this->getMockBuilder(LoginComponent::class)
            ->onlyMethods(['getController'])
            ->setConstructorArgs([$registry, $failureConfig])
            ->getMock();

        $Login->expects($this->atLeastOnce())
            ->method('getController')
            ->willReturn($this->Trait);
        $this->Trait->expects($this->atLeastOnce())
            ->method('loadComponent')
            ->with(
                $this->equalTo('CakeDC/Users.Login'),
                $this->equalTo($failureConfig),
            )
            ->willReturn($Login);

        if ($method === 'login') {
            $this->Trait->expects($this->never())
                ->method('redirect');
            $result = $this->Trait->$method();
            $this->assertNull($result);
        } else {
            $this->Trait->expects($this->once())
                ->method('redirect')
                ->with(['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'login', 'prefix' => false])
                ->willReturn(new Response());
            $result = $this->Trait->$method();
            $this->assertInstanceOf(Response::class, $result);
        }
    }

    /**
     * test socialLogin success
     *
     * @return void
     */
    public function testSocialLoginSuccess(): void
    {
        $identifier = new PasswordIdentifier();
        $FormAuth = new FormAuthenticator($identifier);
        $SessionAuth = new SessionAuthenticator($identifier);

        $sessionFailure = new Failure(
            $SessionAuth,
            new Result(null, Result::FAILURE_IDENTITY_NOT_FOUND),
        );
        $formFailure = new Failure(
            $FormAuth,
            new Result(null, Result::FAILURE_CREDENTIALS_MISSING, [
                'Password' => [],
            ]),
        );
        $failures = [$sessionFailure, $formFailure];

        $this->mockDispatchEvent(new Event('event'));

        $request = $this->getMockBuilder('Cake\Http\ServerRequest')
            ->onlyMethods(['is'])
            ->getMock();
        $request->expects($this->never())
            ->method('is')
            ->with('post');
        $this->Trait->setRequest($request);

        $flash = $this->mockFlash();
        $this->mockAuthentication(['id' => 1], $failures);
        $flash->expects('set')->never();
        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with($this->successLoginRedirect)
            ->willReturn(new Response());

        $registry = new ComponentRegistry(new Controller(new ServerRequest()));
        $config = [
            'component' => 'CakeDC/Users.Login',
            'defaultMessage' => __d('cake_d_c/users', 'Could not proceed with social account. Please try again'),
            'messages' => [
                SocialAuthenticator::FAILURE_USER_NOT_ACTIVE => __d(
                    'cake_d_c/users',
                    'Your user has not been validated yet. Please check your inbox for instructions',
                ),
                SocialAuthenticator::FAILURE_ACCOUNT_NOT_ACTIVE => __d(
                    'cake_d_c/users',
                    'Your social account has not been validated yet. Please check your inbox for instructions',
                ),
            ],
            'targetAuthenticator' => SocialAuthenticator::class,
        ];
        $Login = $this->getMockBuilder(LoginComponent::class)
            ->onlyMethods(['getController'])
            ->setConstructorArgs([$registry, $config])
            ->getMock();

        $Login->expects($this->atLeastOnce())
            ->method('getController')
            ->willReturn($this->Trait);
        $this->Trait->expects($this->atLeastOnce())
            ->method('loadComponent')
            ->with(
                $this->equalTo('CakeDC/Users.Login'),
                $this->equalTo($config),
            )
            ->willReturn($Login);

        $result = $this->Trait->socialLogin();
        $this->assertInstanceOf(Response::class, $result);
    }
}

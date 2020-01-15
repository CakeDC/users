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
use Cake\Controller\ComponentRegistry;
use Cake\Event\Event;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use CakeDC\Auth\Authentication\Failure;
use CakeDC\Auth\Authenticator\FormAuthenticator;
use CakeDC\Users\Authenticator\SocialAuthenticator;
use CakeDC\Users\Controller\Component\LoginComponent;

class SocialTraitTest extends BaseTraitTest
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
        $request = new ServerRequest();
        $this->Trait = $this->getMockBuilder($this->traitClassName)
            ->setMethods(['dispatchEvent', 'redirect', 'set', 'loadComponent'])
            ->getMock();

        $this->Trait->request = $request;
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
     * test socialLogin success
     *
     * @return void
     */
    public function testSocialEmailSuccess()
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
        $this->Trait->setRequest($this->getMockBuilder('Cake\Http\ServerRequest')
            ->setMethods(['is'])
            ->getMock());
        $this->Trait->getRequest()->expects($this->any())
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

        $result = $this->Trait->socialEmail();
        $this->assertInstanceOf(Response::class, $result);
    }
}

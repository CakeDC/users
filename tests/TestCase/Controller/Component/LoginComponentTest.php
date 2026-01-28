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

namespace CakeDC\Users\Test\TestCase\Controller\Component;

use Authentication\AuthenticationService;
use Authentication\Authenticator\Result;
use Authentication\Identity;
use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\Http\ServerRequest;
use Cake\ORM\Entity;
use Cake\TestSuite\TestCase;
use CakeDC\Users\Controller\Component\LoginComponent;

class LoginComponentTest extends TestCase
{
    protected ServerRequest $request;
    protected Controller $controller;
    protected LoginComponent $component;
    protected Entity $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->request = new ServerRequest();
        $this->controller = new \CakeDC\Users\Controller\UsersController($this->request);
        $registry = new ComponentRegistry($this->controller);
        $this->component = new LoginComponent($registry);
        $this->component->initialize([]);
        $this->user = new \CakeDC\Users\Model\Entity\User([
            'id' => 'd602b053-9d10-4a1c-b05d-5674f68a1f3a',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);
    }

    public function testLoginRehash()
    {
        $authenticationService = $this->getMockBuilder(AuthenticationService::class)->getMock();
        $result = $this->getMockBuilder(Result::class)->disableOriginalConstructor()->getMock();
        $result->expects($this->once())->method('isValid')->willReturn(true);
        $authenticationService->expects($this->once())->method('getResult')->willReturn($result);
        $this->request = $this->request->withAttribute('authentication', $authenticationService);
        $identity = $this->getMockBuilder(Identity::class)->disableOriginalConstructor()->getMock();
        $identity->expects($this->once())->method('getOriginalData')->willReturn($this->user);
        $this->request = $this->request->withAttribute('authentication', $authenticationService);
        $this->request = $this->request->withAttribute('identity', $identity);
        $this->controller->setRequest($this->request);
        $this->component->handleLogin(false, false);
    }

    /**
     * Test that password rehash uses the new authenticators config path
     *
     * This test verifies that when Auth.PasswordRehash.authenticators is configured,
     * the code correctly traverses: service->authenticators()->get('Form')->getIdentifier()->get('Password')
     *
     * @return void
     */
    public function testPasswordRehashUsesAuthenticatorsConfig()
    {
        // Configure the new authenticators path (clear any deprecated identifiers config)
        \Cake\Core\Configure::write('Auth.PasswordRehash', [
            'authenticators' => [
                'Form' => 'Password',
            ],
        ]);

        // Create a password identifier that needs rehash
        $passwordIdentifier = $this->getMockBuilder(\Authentication\Identifier\PasswordIdentifier::class)
            ->onlyMethods(['needsPasswordRehash'])
            ->getMock();
        // This expectation verifies the code reaches the identifier through authenticators path
        $passwordIdentifier->expects($this->once())
            ->method('needsPasswordRehash')
            ->willReturn(false); // Return false to skip the actual save

        $identifiers = new \Authentication\Identifier\IdentifierCollection([]);
        $identifiers->set('Password', $passwordIdentifier);

        // Mock Form authenticator that returns our identifier collection
        $formAuthenticator = $this->getMockBuilder(\Authentication\Authenticator\FormAuthenticator::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getIdentifier'])
            ->getMock();
        // This expectation verifies getIdentifier() is called on the Form authenticator
        $formAuthenticator->expects($this->atLeastOnce())
            ->method('getIdentifier')
            ->willReturn($identifiers);

        // Mock authenticator collection
        $authenticators = $this->getMockBuilder(\Authentication\Authenticator\AuthenticatorCollection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['has', 'get'])
            ->getMock();
        // This expectation verifies the code checks for 'Form' authenticator
        $authenticators->expects($this->atLeastOnce())
            ->method('has')
            ->with('Form')
            ->willReturn(true);
        // This expectation verifies the code gets the 'Form' authenticator
        $authenticators->expects($this->atLeastOnce())
            ->method('get')
            ->with('Form')
            ->willReturn($formAuthenticator);

        // Mock authentication service
        $authenticationService = $this->getMockBuilder(AuthenticationService::class)
            ->onlyMethods(['getResult', 'authenticators', 'identifiers'])
            ->getMock();

        $result = new Result($this->user->toArray(), Result::SUCCESS);
        $authenticationService->expects($this->once())->method('getResult')->willReturn($result);
        // This expectation verifies authenticators() is called (new config path)
        $authenticationService->expects($this->atLeastOnce())
            ->method('authenticators')
            ->willReturn($authenticators);
        // identifiers() should not be called for rehash since we cleared the deprecated config
        $authenticationService->expects($this->never())
            ->method('identifiers');

        $identity = $this->getMockBuilder(Identity::class)
            ->disableOriginalConstructor()
            ->getMock();
        $identity->expects($this->once())
            ->method('getOriginalData')
            ->willReturn($this->user);

        $this->request = $this->request->withAttribute('authentication', $authenticationService);
        $this->request = $this->request->withAttribute('identity', $identity);
        $this->request = $this->request->withMethod('POST');
        $this->controller->setRequest($this->request);

        $this->component->handleLogin(false, false);
    }
}

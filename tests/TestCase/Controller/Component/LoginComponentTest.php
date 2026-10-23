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

use Authentication\Authenticator\Result;
use Authentication\Identity;
use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\ORM\Entity;
use Cake\TestSuite\TestCase;
use CakeDC\Auth\Authentication\AuthenticationService;
use CakeDC\Users\Controller\Component\LoginComponent;
use CakeDC\Users\Controller\UsersController;
use CakeDC\Users\Model\Entity\User;

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
        $this->controller = new UsersController($this->request);
        $registry = new ComponentRegistry($this->controller);
        $this->component = new LoginComponent($registry);
        $this->component->initialize([]);
        $this->user = new User([
            'id' => 'd602b053-9d10-4a1c-b05d-5674f68a1f3a',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);
    }

    public function testLoginRehash(): void
    {
        $authenticationService = $this->getMockBuilder(AuthenticationService::class)->getMock();
        $result = $this->getMockBuilder(Result::class)->disableOriginalConstructor()->getMock();
        $result->expects($this->once())->method('isValid')->willReturn(true);
        $authenticationService->expects($this->once())->method('getResult')->willReturn($result);
        $this->request = $this->request->withAttribute('authentication', $authenticationService);
        $identity = $this->getMockBuilder(Identity::class)->disableOriginalConstructor()->getMock();
        $identity->expects($this->once())->method('getOriginalData')->willReturn($this->user);
        $this->request = $this->request->withAttribute('identity', $identity);
        $this->controller->setRequest($this->request);
        $result = $this->component->handleLogin(false, false);
        $this->assertInstanceOf(Response::class, $result);
    }
}

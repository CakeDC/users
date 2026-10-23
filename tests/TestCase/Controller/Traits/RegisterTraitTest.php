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

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\Routing\Router;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

#[AllowMockObjectsWithoutExpectations]
class RegisterTraitTest extends BaseTrait
{
    /**
     * setUp
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->traitClassName = 'CakeDC\Users\Controller\UsersController';
        $this->traitMockMethods = ['validate', 'dispatchEvent', 'set', 'validateReCaptcha', 'redirect', 'getUsersTable'];
        $this->mockDefaultEmail = true;
        parent::setUp();

        $this->Trait->setRequest(new ServerRequest());
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
    public function testValidateEmail(): void
    {
        $token = 'token';
        $this->Trait->expects($this->once())
            ->method('validate')
            ->with('email', $token);
        $this->Trait->validateEmail($token);
    }

    /**
     * test
     *
     * @return void
     */
    public function testRegister(): void
    {
        $builder = Router::createRouteBuilder('/');
        $builder->connect('/users/validate-email/*', [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'validateEmail',
        ]);

        $this->assertEquals(0, $this->table->find()->where(['username' => 'testRegistration'])->count());
        $this->mockRequestPost();
        $this->mockAuthentication(null, [], false);
        $flash = $this->mockFlash();
        $this->mockDispatchEvent();
        $flash->expects('set')
            ->with('Please validate your account before log in', ['element' => 'success']);
        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with(['action' => 'login'])
            ->willReturn(new Response());
        $this->Trait->getRequest()->expects($this->once())
            ->method('getData')
            ->willReturn([
                'username' => 'testRegistration',
                'password' => 'password',
                'email' => 'test-registration@example.com',
                'password_confirm' => 'password',
                'tos' => '1',
            ]);

        $this->Trait->register();

        $this->assertEquals(1, $this->table->find()->where(['username' => 'testRegistration'])->count());
    }

    /**
     * Triggering beforeRegister event and not able to register the user
     *
     * @return void
     */
    public function testRegisterWithEventFalseResult(): void
    {
        $this->assertEquals(0, $this->table->find()->where(['username' => 'testRegistration'])->count());
        $this->mockRequestPost();
        $this->mockAuthentication(null, [], false);
        $flash = $this->mockFlash();
        $this->mockDispatchEvent(new Event('Users.Component.UsersAuth.beforeRegister'), ['username' => 'hello']);
        $flash->expects('set')
            ->with('The user could not be saved', ['element' => 'error']);
        $this->Trait->expects($this->never())
            ->method('redirect');
        $this->Trait->getRequest()->expects($this->never())
            ->method('is');

        $this->Trait->register();
        $this->assertEquals(0, $this->table->find()->where(['username' => 'testRegistration'])->count());
    }

    /**
     * Triggering beforeRegister event and registering the user successfully
     *
     * @return void
     */
    public function testRegisterWithEventSuccessResult(): void
    {
        $builder = Router::createRouteBuilder('/');
        $builder->connect('/users/validate-email/*', [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'validateEmail',
        ]);

        $data = [
            'username' => 'testRegistration',
            'password' => 'password',
            'email' => 'test-registration@example.com',
            'password_confirm' => 'password',
            'tos' => '1',
        ];

        $this->assertEquals(0, $this->table->find()->where(['username' => 'testRegistration'])->count());
        $this->mockRequestPost();
        $this->mockAuthentication(null, [], false);
        $flash = $this->mockFlash();
        $this->mockDispatchEvent(new Event('Users.Component.UsersAuth.beforeRegister'), $data);
        $this->Trait->getRequest()->expects($this->once())
            ->method('getData')
            ->willReturn($data);
        $flash->expects('set')
            ->with('Please validate your account before log in', ['element' => 'success']);
        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with(['action' => 'login'])
            ->willReturn(new Response());
        $this->Trait->getRequest()->expects($this->never())
            ->method('is');

        $this->Trait->register();
        $this->assertEquals(1, $this->table->find()->where(['username' => 'testRegistration'])->count());
    }

    /**
     * test
     *
     * @return void
     */
    public function testRegisterReCaptcha(): void
    {
        $builder = Router::createRouteBuilder('/');
        $builder->connect('/users/validate-email/*', [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'validateEmail',
        ]);

        Configure::write('Users.reCaptcha.registration', true);
        $this->assertEquals(0, $this->table->find()->where(['username' => 'testRegistration'])->count());
        $this->mockRequestPost();
        $this->mockAuthentication(null, [], false);
        $flash = $this->mockFlash();
        $this->mockDispatchEvent();
        $flash->expects('set')
            ->with('Please validate your account before log in', ['element' => 'success']);
        $this->Trait->expects($this->once())
            ->method('validateRecaptcha')
            ->willReturn(true);
        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with(['action' => 'login'])
            ->willReturn(new Response());
        $this->Trait->getRequest()->expects($this->exactly(2))
            ->method('getData')
            ->willReturnMap([
                [[
                    'username' => 'testRegistration',
                    'password' => 'password',
                    'email' => 'test-registration@example.com',
                    'password_confirm' => 'password',
                    'tos' => '1',
                ]],
                ['g-recaptcha-response', 'recaptcha-response'],
            ]);

        $this->Trait->register();

        $this->assertEquals(1, $this->table->find()->where(['username' => 'testRegistration'])->count());
    }

    /**
     * test
     *
     * @return void
     */
    public function testRegisterValidationErrors(): void
    {
        Configure::write('Users.reCaptcha.registration', true);
        $this->assertEquals(0, $this->table->find()->where(['username' => 'testRegistration'])->count());
        $this->mockRequestPost();
        $this->mockAuthentication(null, [], false);
        $flash = $this->mockFlash();
        $this->mockDispatchEvent();
        $flash->expects('set')
            ->with('The user could not be saved', ['element' => 'error']);
        $this->Trait->expects($this->once())
            ->method('validateRecaptcha')
            ->willReturn(true);
        $this->Trait->expects($this->never())
            ->method('redirect');
        $this->Trait->getRequest()->expects($this->exactly(2))
            ->method('getData')
            ->willReturnMap([
                [[
                    'username' => 'testRegistration',
                    'password' => 'password',
                    'email' => 'test-registration@example.com',
                    'password_confirm' => 'not-matching',
                    'tos' => '1',
                ]],
                ['g-recaptcha-response', 'recaptcha-response'],
            ]);

        $this->Trait->register();

        $this->assertEquals(0, $this->table->find()->where(['username' => 'testRegistration'])->count());
    }

    /**
     * test
     *
     * @return void
     */
    public function testRegisterRecaptchaNotValid(): void
    {
        Configure::write('Users.reCaptcha.registration', true);
        $this->assertEquals(0, $this->table->find()->where(['username' => 'testRegistration'])->count());
        $this->mockRequestPost();
        $this->mockAuthentication(null, [], false);
        $flash = $this->mockFlash();
        $this->mockDispatchEvent();
        $flash->expects('set')
            ->with('Invalid reCaptcha', ['element' => 'error']);
        $this->Trait->expects($this->once())
            ->method('validateRecaptcha')
            ->willReturn(false);
        $this->Trait->getRequest()->expects($this->exactly(2))
            ->method('getData')
            ->willReturnMap([
                [[
                    'username' => 'testRegistration',
                    'password' => 'password',
                    'email' => 'test-registration@example.com',
                    'password_confirm' => 'password',
                    'tos' => '1',
                ]],
                ['g-recaptcha-response', 'recaptcha-response'],
            ]);

        $this->Trait->register();

        $this->assertEquals(0, $this->table->find()->where(['username' => 'testRegistration'])->count());
    }

    /**
     * test
     *
     * @return void
     */
    public function testRegisterGet(): void
    {
        $this->assertEquals(0, $this->table->find()->where(['username' => 'testRegistration'])->count());
        $this->mockRequestGet();
        $this->mockAuthentication(null, [], false);
        $flash = $this->mockFlash();
        $this->mockDispatchEvent();
        $flash->expects('set')->never();
        $this->Trait->expects($this->never())
            ->method('validateRecaptcha');
        $this->Trait->expects($this->never())
            ->method('redirect');
        $this->Trait->register();

        $this->assertEquals(0, $this->table->find()->where(['username' => 'testRegistration'])->count());
    }

    /**
     * test
     *
     * @return void
     */
    public function testRegisterRecaptchaDisabled(): void
    {
        $builder = Router::createRouteBuilder('/');
        $builder->connect('/users/validate-email/*', [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'validateEmail',
        ]);

        Configure::write('Users.Registration.reCaptcha', false);
        $this->assertEquals(0, $this->table->find()->where(['username' => 'testRegistration'])->count());
        $this->mockRequestPost();
        $this->mockAuthentication(null, [], false);
        $flash = $this->mockFlash();
        $this->mockDispatchEvent();
        $flash->expects('set')
            ->with('Please validate your account before log in', ['element' => 'success']);
        $this->Trait->expects($this->never())
            ->method('validateRecaptcha');
        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with(['action' => 'login'])
            ->willReturn(new Response());
        $this->Trait->getRequest()->expects($this->once())
            ->method('getData')
            ->with()
            ->willReturn([
                'username' => 'testRegistration',
                'password' => 'password',
                'email' => 'test-registration@example.com',
                'password_confirm' => 'password',
                'tos' => '1',
            ]);

        $this->Trait->register();

        $this->assertEquals(1, $this->table->find()->where(['username' => 'testRegistration'])->count());
    }

    /**
     * test
     *
     * @return void
     */
    public function testRegisterNotEnabled(): void
    {
        $this->expectException(NotFoundException::class);
        Configure::write('Users.Registration.active', false);
        $this->mockRequestPost();
        $this->mockAuthentication(null, [], false);
        $this->mockFlash();
        $this->Trait->expects($this->never())
            ->method('dispatchEvent');
        $this->Trait->register();
    }

    /**
     * test
     *
     * @return void
     */
    public function testRegisterLoggedInUserAllowed(): void
    {
        $builder = Router::createRouteBuilder('/');
        $builder->connect('/users/validate-email/*', [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'validateEmail',
        ]);

        Configure::write('Users.Registration.allowLoggedIn', true);
        $this->assertEquals(0, $this->table->find()->where(['username' => 'testRegistration'])->count());
        $this->mockRequestPost();
        $this->mockAuthLoggedIn();
        $flash = $this->mockFlash();
        $this->mockDispatchEvent();
        $flash->expects('set')
            ->with('Please validate your account before log in', ['element' => 'success']);
        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with(['action' => 'login'])
            ->willReturn(new Response());
        $this->Trait->getRequest()->expects($this->once())
            ->method('getData')
            ->with()
            ->willReturn([
                'username' => 'testRegistration',
                'password' => 'password',
                'email' => 'test-registration@example.com',
                'password_confirm' => 'password',
                'tos' => '1',
            ]);

        $this->Trait->register();

        $this->assertEquals(1, $this->table->find()->where(['username' => 'testRegistration'])->count());
    }

    /**
     * test
     *
     * @return void
     */
    public function testRegisterLoggedInUserNotAllowed(): void
    {
        Configure::write('Users.Registration.allowLoggedIn', false);
        $this->assertEquals(0, $this->table->find()->where(['username' => 'testRegistration'])->count());
        $this->mockRequestPost();
        $this->mockAuthLoggedIn();
        $flash = $this->mockFlash();
        $flash->expects('set')
            ->with('You must log out to register a new user account', ['element' => 'error']);
        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with(Configure::read('Users.Profile.route'));
        $this->Trait->getRequest()->expects($this->never())
            ->method('getData')
            ->with();

        $this->Trait->register();
    }

    /**
     * test
     *
     * @return void
     */
    public function testNotShowingVerboseErrorOnRegisterWithDefaultConfig(): void
    {
        //register user and not validate the email
        $this->testRegister();

        $this->mockRequestPost();
        $this->mockAuthentication(null, [], false);
        $flash = $this->mockFlash();
        $this->mockDispatchEvent();
        $flash->expects('set')
            ->with('The user could not be saved', ['element' => 'error']);

        $this->Trait->getRequest()->expects($this->once())
            ->method('getData')
            ->willReturn([
                'username' => 'testRegistration',
                'password' => 'password',
                'email' => 'test-registration@example.com',
                'password_confirm' => 'password',
                'tos' => '1',
            ]);

        $this->Trait->register();
    }

    /**
     * test
     *
     * @return void
     */
    public function testShowingVerboseErrorOnRegisterWithUpdatedConfig(): void
    {
        //register user and not validate the email
        $this->testRegister();

        $this->mockRequestPost();
        $this->mockAuthentication(null, [], false);
        $flash = $this->mockFlash();
        $this->mockDispatchEvent();

        $flash->expects('set')
            ->with('Email already exists', ['element' => 'error']);

        $this->Trait->getRequest()->expects($this->once())
            ->method('getData')
            ->willReturn([
                'username' => 'testRegistration1',
                'password' => 'password',
                'email' => 'test-registration@example.com',
                'password_confirm' => 'password',
                'tos' => '1',
            ]);
        Configure::write('Users.Registration.showVerboseError', true);
        $this->Trait->register();
    }
}

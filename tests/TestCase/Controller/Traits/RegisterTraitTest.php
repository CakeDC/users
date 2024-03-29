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

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\ServerRequest;
use Cake\Routing\Router;

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
        $this->skipUsersMock = true;
        parent::setUp();

        $this->Trait->setRequest(new ServerRequest());
        $this->Trait = $this->getMockBuilder('CakeDC\Users\Controller\UsersController')
            ->onlyMethods($this->traitMockMethods)
            ->setConstructorArgs([new ServerRequest()])
            ->getMock();

        $this->Trait->expects($this->any())
            ->method('getUsersTable')
            ->will($this->returnValue($this->table));
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
    public function testValidateEmail()
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
    public function testRegister()
    {
        $builder = Router::createRouteBuilder('/');
        $builder->connect('/users/validate-email/*', [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'validateEmail',
        ]);

        $this->assertEquals(0, $this->table->find()->where(['username' => 'testRegistration'])->count());
        $this->_mockRequestPost();
        $this->_mockAuthentication();
        $this->_mockFlash();
        $this->_mockDispatchEvent();
        $this->Trait->Flash->expects($this->once())
            ->method('success')
            ->with('Please validate your account before log in');
        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with(['action' => 'login']);
        $this->Trait->getRequest()->expects($this->once())
            ->method('getData')
            ->will($this->returnValue([
                'username' => 'testRegistration',
                'password' => 'password',
                'email' => 'test-registration@example.com',
                'password_confirm' => 'password',
                'tos' => 1,
            ]));

        $this->Trait->register();

        $this->assertEquals(1, $this->table->find()->where(['username' => 'testRegistration'])->count());
    }

    /**
     * Triggering beforeRegister event and not able to register the user
     *
     * @return void
     */
    public function testRegisterWithEventFalseResult()
    {
        $this->assertEquals(0, $this->table->find()->where(['username' => 'testRegistration'])->count());
        $this->_mockRequestPost();
        $this->_mockAuthentication();
        $this->_mockFlash();
        $this->_mockDispatchEvent(new Event('Users.Component.UsersAuth.beforeRegister'), ['username' => 'hello']);
        $this->Trait->Flash->expects($this->once())
            ->method('error')
            ->with('The user could not be saved');
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
    public function testRegisterWithEventSuccessResult()
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
            'tos' => 1,
        ];

        $this->assertEquals(0, $this->table->find()->where(['username' => 'testRegistration'])->count());
        $this->_mockRequestPost();
        $this->_mockAuthentication();
        $this->_mockFlash();
        $this->_mockDispatchEvent(new Event('Users.Component.UsersAuth.beforeRegister'), $data);
        $this->Trait->getRequest()->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));
        $this->Trait->Flash->expects($this->once())
            ->method('success')
            ->with('Please validate your account before log in');
        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with(['action' => 'login']);
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
    public function testRegisterReCaptcha()
    {
        $builder = Router::createRouteBuilder('/');
        $builder->connect('/users/validate-email/*', [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'validateEmail',
        ]);

        Configure::write('Users.reCaptcha.registration', true);
        $this->assertEquals(0, $this->table->find()->where(['username' => 'testRegistration'])->count());
        $this->_mockRequestPost();
        $this->_mockAuthentication();
        $this->_mockFlash();
        $this->_mockDispatchEvent();
        $this->Trait->Flash->expects($this->once())
            ->method('success')
            ->with('Please validate your account before log in');
        $this->Trait->expects($this->once())
            ->method('validateRecaptcha')
            ->will($this->returnValue(true));
        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with(['action' => 'login']);
        $this->Trait->getRequest()->expects($this->any())
            ->method('getData')
            ->with()
            ->will($this->returnValue([
                'username' => 'testRegistration',
                'password' => 'password',
                'email' => 'test-registration@example.com',
                'password_confirm' => 'password',
                'tos' => 1,
            ]));

        $this->Trait->register();

        $this->assertEquals(1, $this->table->find()->where(['username' => 'testRegistration'])->count());
    }

    /**
     * test
     *
     * @return void
     */
    public function testRegisterValidationErrors()
    {
        Configure::write('Users.reCaptcha.registration', true);
        $this->assertEquals(0, $this->table->find()->where(['username' => 'testRegistration'])->count());
        $this->_mockRequestPost();
        $this->_mockAuthentication();
        $this->_mockFlash();
        $this->_mockDispatchEvent();
        $this->Trait->Flash->expects($this->once())
            ->method('error')
            ->with('The user could not be saved');
        $this->Trait->expects($this->once())
            ->method('validateRecaptcha')
            ->will($this->returnValue(true));
        $this->Trait->expects($this->never())
            ->method('redirect');
        $this->Trait->getRequest()->expects($this->any())
            ->method('getData')
            ->with()
            ->will($this->returnValue([
                'username' => 'testRegistration',
                'password' => 'password',
                'email' => 'test-registration@example.com',
                'password_confirm' => 'not-matching',
                'tos' => 1,
            ]));

        $this->Trait->register();

        $this->assertEquals(0, $this->table->find()->where(['username' => 'testRegistration'])->count());
    }

    /**
     * test
     *
     * @return void
     */
    public function testRegisterRecaptchaNotValid()
    {
        Configure::write('Users.reCaptcha.registration', true);
        $this->assertEquals(0, $this->table->find()->where(['username' => 'testRegistration'])->count());
        $this->_mockRequestPost();
        $this->_mockAuthentication();
        $this->_mockFlash();
        $this->_mockDispatchEvent();
        $this->Trait->Flash->expects($this->once())
            ->method('error')
            ->with('Invalid reCaptcha');
        $this->Trait->expects($this->any())
            ->method('validateRecaptcha')
            ->will($this->returnValue(false));
        $this->Trait->getRequest()->expects($this->any())
            ->method('getData')
            ->with()
            ->will($this->returnValue([
                'username' => 'testRegistration',
                'password' => 'password',
                'email' => 'test-registration@example.com',
                'password_confirm' => 'password',
                'tos' => 1,
            ]));

        $this->Trait->register();

        $this->assertEquals(0, $this->table->find()->where(['username' => 'testRegistration'])->count());
    }

    /**
     * test
     *
     * @return void
     */
    public function testRegisterGet()
    {
        $this->assertEquals(0, $this->table->find()->where(['username' => 'testRegistration'])->count());
        $this->_mockRequestGet();
        $this->_mockAuthentication();
        $this->_mockFlash();
        $this->_mockDispatchEvent();
        $this->Trait->Flash->expects($this->never())
            ->method('success');
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
    public function testRegisterRecaptchaDisabled()
    {
        $builder = Router::createRouteBuilder('/');
        $builder->connect('/users/validate-email/*', [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'validateEmail',
        ]);

        Configure::write('Users.Registration.reCaptcha', false);
        $this->assertEquals(0, $this->table->find()->where(['username' => 'testRegistration'])->count());
        $this->_mockRequestPost();
        $this->_mockAuthentication();
        $this->_mockFlash();
        $this->_mockDispatchEvent();
        $this->Trait->Flash->expects($this->once())
            ->method('success')
            ->with('Please validate your account before log in');
        $this->Trait->expects($this->never())
            ->method('validateRecaptcha');
        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with(['action' => 'login']);
        $this->Trait->getRequest()->expects($this->once())
            ->method('getData')
            ->with()
            ->will($this->returnValue([
                'username' => 'testRegistration',
                'password' => 'password',
                'email' => 'test-registration@example.com',
                'password_confirm' => 'password',
                'tos' => 1,
            ]));

        $this->Trait->register();

        $this->assertEquals(1, $this->table->find()->where(['username' => 'testRegistration'])->count());
    }

    /**
     * test
     *
     * @return void
     */
    public function testRegisterNotEnabled()
    {
        $this->expectException(NotFoundException::class);
        Configure::write('Users.Registration.active', false);
        $this->_mockRequestPost();
        $this->_mockAuthentication();
        $this->_mockFlash();
        $this->_mockDispatchEvent();
        $this->Trait->register();
    }

    /**
     * test
     *
     * @return void
     */
    public function testRegisterLoggedInUserAllowed()
    {
        $builder = Router::createRouteBuilder('/');
        $builder->connect('/users/validate-email/*', [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'validateEmail',
        ]);

        Configure::write('Users.Registration.allowLoggedIn', true);
        $this->assertEquals(0, $this->table->find()->where(['username' => 'testRegistration'])->count());
        $this->_mockRequestPost();
        $this->_mockAuthLoggedIn();
        $this->_mockFlash();
        $this->_mockDispatchEvent();
        $this->Trait->Flash->expects($this->once())
            ->method('success')
            ->with('Please validate your account before log in');
        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with(['action' => 'login']);
        $this->Trait->getRequest()->expects($this->once())
            ->method('getData')
            ->with()
            ->will($this->returnValue([
                'username' => 'testRegistration',
                'password' => 'password',
                'email' => 'test-registration@example.com',
                'password_confirm' => 'password',
                'tos' => 1,
            ]));

        $this->Trait->register();

        $this->assertEquals(1, $this->table->find()->where(['username' => 'testRegistration'])->count());
    }

    /**
     * test
     *
     * @return void
     */
    public function testRegisterLoggedInUserNotAllowed()
    {
        Configure::write('Users.Registration.allowLoggedIn', false);
        $this->assertEquals(0, $this->table->find()->where(['username' => 'testRegistration'])->count());
        $this->_mockRequestPost();
        $this->_mockAuthLoggedIn();
        $this->_mockFlash();
        $this->_mockDispatchEvent();
        $this->Trait->Flash->expects($this->once())
            ->method('error')
            ->with('You must log out to register a new user account');
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
    public function testNotShowingVerboseErrorOnRegisterWithDefaultConfig()
    {
        //register user and not validate the email
        $this->testRegister();

        $this->_mockRequestPost();
        $this->_mockAuthentication();
        $this->_mockFlash();
        $this->_mockDispatchEvent();
        $this->Trait->Flash->expects($this->once())
            ->method('error')
            ->with('The user could not be saved');

        $this->Trait->getRequest()->expects($this->once())
            ->method('getData')
            ->will($this->returnValue([
                'username' => 'testRegistration',
                'password' => 'password',
                'email' => 'test-registration@example.com',
                'password_confirm' => 'password',
                'tos' => 1,
            ]));

        $this->Trait->register();
    }

    /**
     * test
     *
     * @return void
     */
    public function testShowingVerboseErrorOnRegisterWithUpdatedConfig()
    {
        //register user and not validate the email
        $this->testRegister();

        $this->_mockRequestPost();
        $this->_mockAuthentication();
        $this->_mockFlash();
        $this->_mockDispatchEvent();

        $this->Trait->Flash->expects($this->once())
            ->method('error')
            ->with('Email already exists');

        $this->Trait->getRequest()->expects($this->once())
            ->method('getData')
            ->will($this->returnValue([
                'username' => 'testRegistration1',
                'password' => 'password',
                'email' => 'test-registration@example.com',
                'password_confirm' => 'password',
                'tos' => 1,
            ]));
        Configure::write('Users.Registration.showVerboseError', true);
        $this->Trait->register();
    }
}

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

namespace CakeDC\Users\Test\TestCase\Controller\Traits;

use CakeDC\Users\Test\TestCase\Controller\Traits\BaseTraitTest;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Mailer\Email;
use Cake\ORM\TableRegistry;

class RegisterTraitTest extends BaseTraitTest
{
    /**
     * setUp
     *
     * @return void
     */
    public function setUp()
    {
        $this->traitClassName = 'CakeDC\Users\Controller\Traits\RegisterTrait';
        $this->traitMockMethods = ['validate', 'dispatchEvent', 'set', 'validateReCaptcha', 'redirect'];
        $this->mockDefaultEmail = true;
        parent::setUp();

        Plugin::routes('CakeDC/Users');
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
        $this->assertEquals(0, $this->table->find()->where(['username' => 'testRegistration'])->count());
        $this->_mockRequestPost();
        $this->_mockAuth();
        $this->_mockFlash();
        $this->_mockDispatchEvent();
        $this->Trait->Flash->expects($this->once())
            ->method('success')
            ->with('Please validate your account before log in');
        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with(['action' => 'login']);
        $this->Trait->request->expects($this->once())
            ->method('getData')
            ->will($this->returnValue([
                'username' => 'testRegistration',
                'password' => 'password',
                'email' => 'test-registration@example.com',
                'password_confirm' => 'password',
                'tos' => 1
            ]));

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
        Configure::write('Users.reCaptcha.registration', true);
        $this->assertEquals(0, $this->table->find()->where(['username' => 'testRegistration'])->count());
        $this->_mockRequestPost();
        $this->_mockAuth();
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
        $this->Trait->request->expects($this->at(0))
            ->method('getData')
            ->with()
            ->will($this->returnValue([
                'username' => 'testRegistration',
                'password' => 'password',
                'email' => 'test-registration@example.com',
                'password_confirm' => 'password',
                'tos' => 1
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
        $this->_mockAuth();
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
        $this->Trait->request->expects($this->at(0))
            ->method('getData')
            ->with()
            ->will($this->returnValue([
                'username' => 'testRegistration',
                'password' => 'password',
                'email' => 'test-registration@example.com',
                'password_confirm' => 'not-matching',
                'tos' => 1
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
        $this->_mockAuth();
        $this->_mockFlash();
        $this->_mockDispatchEvent();
        $this->Trait->Flash->expects($this->once())
            ->method('error')
            ->with('Invalid reCaptcha');
        $this->Trait->expects($this->once())
            ->method('validateRecaptcha')
            ->will($this->returnValue(false));
        $this->Trait->request->expects($this->at(0))
            ->method('getData')
            ->with()
            ->will($this->returnValue([
                'username' => 'testRegistration',
                'password' => 'password',
                'email' => 'test-registration@example.com',
                'password_confirm' => 'password',
                'tos' => 1
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
        $this->_mockAuth();
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
        Configure::write('Users.Registration.reCaptcha', false);
        $this->assertEquals(0, $this->table->find()->where(['username' => 'testRegistration'])->count());
        $this->_mockRequestPost();
        $this->_mockAuth();
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
        $this->Trait->request->expects($this->at(0))
            ->method('getData')
            ->with()
            ->will($this->returnValue([
                'username' => 'testRegistration',
                'password' => 'password',
                'email' => 'test-registration@example.com',
                'password_confirm' => 'password',
                'tos' => 1
            ]));

        $this->Trait->register();

        $this->assertEquals(1, $this->table->find()->where(['username' => 'testRegistration'])->count());
    }

    /**
     * test
     *
     * @return void
     * @expectedException Cake\Network\Exception\NotFoundException
     */
    public function testRegisterNotEnabled()
    {
        Configure::write('Users.Registration.active', false);
        $this->_mockRequestPost();
        $this->_mockAuth();
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
        $this->Trait->request->expects($this->at(0))
            ->method('getData')
            ->with()
            ->will($this->returnValue([
                'username' => 'testRegistration',
                'password' => 'password',
                'email' => 'test-registration@example.com',
                'password_confirm' => 'password',
                'tos' => 1
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
        $this->Trait->request->expects($this->never())
            ->method('getData')
            ->with();

        $this->Trait->register();
    }
}

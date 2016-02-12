<?php
/**
 * Copyright 2010 - 2015, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2015, Cake Development Corporation (http://cakedc.com)
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
    public function testRegisterHappy()
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
                ->method('validateRecaptcha')
                ->will($this->returnValue(true));
        $this->Trait->expects($this->once())
                ->method('redirect')
                ->with(['action' => 'login']);
        $this->Trait->request->data = [
            'username' => 'testRegistration',
            'password' => 'password',
            'email' => 'test-registration@example.com',
            'password_confirm' => 'password',
            'tos' => 1
        ];

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
        $this->Trait->request->data = [
            'username' => 'testRegistration',
            'password' => 'password',
            'email' => 'test-registration@example.com',
            'password_confirm' => 'not-matching',
            'tos' => 1
        ];

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
        $this->assertEquals(0, $this->table->find()->where(['username' => 'testRegistration'])->count());
        $this->_mockRequestPost();
        $this->_mockAuth();
        $this->_mockFlash();
        $this->_mockDispatchEvent();
        $this->Trait->Flash->expects($this->once())
                ->method('error')
                ->with('The reCaptcha could not be validated');
        $this->Trait->expects($this->once())
                ->method('validateRecaptcha')
                ->will($this->returnValue(false));
        $this->Trait->request->data = [
            'username' => 'testRegistration',
            'password' => 'password',
            'email' => 'test-registration@example.com',
            'password_confirm' => 'password',
            'tos' => 1
        ];

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
        $recaptcha = Configure::read('Users.Registration.reCaptcha');
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
        $this->Trait->request->data = [
            'username' => 'testRegistration',
            'password' => 'password',
            'email' => 'test-registration@example.com',
            'password_confirm' => 'password',
            'tos' => 1
        ];

        $this->Trait->register();

        $this->assertEquals(1, $this->table->find()->where(['username' => 'testRegistration'])->count());
        Configure::write('Users.Registration.reCaptcha', $recaptcha);
    }

    /**
     * test
     *
     * @return void
     * @expectedException Cake\Network\Exception\NotFoundException
     */
    public function testRegisterNotEnabled()
    {
        $active = Configure::read('Users.Registration.active');
        Configure::write('Users.Registration.active', false);
        $this->_mockRequestPost();
        $this->_mockAuth();
        $this->_mockFlash();
        $this->_mockDispatchEvent();
        $this->Trait->register();
        Configure::write('Users.Registration.active', $active);
    }
}

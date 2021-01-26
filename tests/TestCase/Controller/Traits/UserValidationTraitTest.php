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

use Cake\Event\Event;

class UserValidationTraitTest extends BaseTraitTest
{
    /**
     * @var \CakeDC\Users\Controller\UsersController
     */
    public $Trait;

    /**
     * setup
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->traitClassName = 'CakeDC\Users\Controller\UsersController';
        $this->traitMockMethods = ['dispatchEvent', 'isStopped', 'redirect', 'getUsersTable', 'set'];
        $this->mockDefaultEmail = true;
        parent::setUp();
    }

    /**
     * test
     *
     * @return void
     */
    public function testValidateHappyEmail()
    {
        $this->_mockFlash();
        $user = $this->table->findByToken('token-3')->first();
        $this->assertFalse($user->active);
        $this->Trait->Flash->expects($this->once())
                ->method('success')
                ->with('User account validated successfully');
        $this->Trait->expects($this->once())
                ->method('redirect')
                ->with(['action' => 'login']);
        $this->Trait->validate('email', 'token-3');
        $user = $this->table->findById($user->id)->first();
        $this->assertTrue($user->active);
    }

    /**
     * test
     *
     * @return void
     */
    public function testValidateHappyEmailWithAfterEmailTokenValidationEvent()
    {
        $event = new Event('event');
        $event->setResult([
            'action' => 'newAction',
        ]);
        $this->Trait->expects($this->once())
            ->method('dispatchEvent')
            ->will($this->returnValue($event));
        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with(['action' => 'newAction']);
        $this->Trait->validate('email', 'token-3');
    }

    /**
     * test
     *
     * @return void
     */
    public function testValidateUserNotFound()
    {
        $this->_mockFlash();
        $this->Trait->Flash->expects($this->once())
                ->method('error')
                ->with('Invalid token or user account already validated');
        $this->Trait->expects($this->once())
                ->method('redirect')
                ->with(['action' => 'login']);
        $this->Trait->validate('email', 'not-found');
    }

    /**
     * test
     *
     * @return void
     */
    public function testValidateTokenExpired()
    {
        $this->_mockFlash();
        $this->Trait->Flash->expects($this->once())
                ->method('error')
                ->with('Token already expired');
        $this->Trait->expects($this->once())
                ->method('redirect')
                ->with(['action' => 'login']);
        $this->Trait->validate('email', '6614f65816754310a5f0553436dd89e9');
    }

    /**
     * test
     *
     * @return void
     */
    public function testValidateTokenExpiredWithOnExpiredEvent()
    {
        $event = new Event('event');
        $event->setResult([
            'action' => 'newAction',
        ]);
        $this->Trait->expects($this->once())
            ->method('dispatchEvent')
            ->will($this->returnValue($event));
        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with(['action' => 'newAction']);
        $this->Trait->validate('email', '6614f65816754310a5f0553436dd89e9');
    }

    /**
     * test
     *
     * @return void
     */
    public function testValidateInvalidOp()
    {
        $this->_mockFlash();
        $this->Trait->Flash->expects($this->once())
                ->method('error')
                ->with('Invalid validation type');
        $this->Trait->expects($this->once())
                ->method('redirect')
                ->with(['action' => 'login']);
        $this->Trait->validate('invalid-op', '6614f65816754310a5f0553436dd89e9');
    }

    /**
     * test
     *
     * @return void
     */
    public function testValidateHappyPassword()
    {
        $this->_mockRequestGet();
        $this->_mockFlash();
        $user = $this->table->findByToken('token-4')->first();
        $this->assertTrue($user->active);
        $this->Trait->Flash->expects($this->once())
                ->method('success')
                ->with('Reset password token was validated successfully');
        $this->Trait->expects($this->once())
                ->method('redirect')
                ->with(['action' => 'changePassword']);
        $this->Trait->validate('password', 'token-4');
        $user = $this->table->findById($user->id)->first();
        $this->assertTrue($user->active);
    }

    /**
     * test
     *
     * @return void
     */
    public function testResendTokenValidationHappy()
    {
        $this->_mockRequestPost();
        $this->_mockFlash();
        $this->Trait->getRequest()->expects($this->once())
                ->method('getData')
                ->with('reference')
                ->will($this->returnValue('user-3'));

        $this->Trait->Flash->expects($this->once())
                ->method('success')
                ->with('Token has been reset successfully. Please check your email.');
        $this->Trait->expects($this->once())
                ->method('redirect')
                ->with(['action' => 'login']);
        $this->Trait->resendTokenValidation();
    }

    /**
     * test
     *
     * @return void
     */
    public function testResendTokenValidationWithAfterResendTokenValidationEvent()
    {
        $this->_mockRequestPost();
        $this->_mockFlash();
        $this->Trait->getRequest()->expects($this->once())
            ->method('getData')
            ->with('reference')
            ->will($this->returnValue('user-3'));

        $event = new Event('event');
        $event->setResult([
            'action' => 'newAction',
        ]);
        $this->Trait->expects($this->once())
            ->method('dispatchEvent')
            ->will($this->returnValue($event));
        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with(['action' => 'newAction']);

        $this->Trait->resendTokenValidation();
    }

    /**
     * test
     *
     * @return void
     */
    public function testResendTokenValidationAlreadyActive()
    {
        $this->_mockRequestPost();
        $this->_mockFlash();
        $this->Trait->getRequest()->expects($this->once())
                ->method('getData')
                ->with('reference')
                ->will($this->returnValue('user-4'));

        $this->Trait->Flash->expects($this->once())
                ->method('error')
                ->with('User user-4 is already active');
        $this->Trait->expects($this->never())
                ->method('redirect')
                ->with(['action' => 'login']);
        $this->Trait->resendTokenValidation();
    }

    /**
     * test
     *
     * @return void
     */
    public function testResendTokenValidationNotFound()
    {
        $this->_mockRequestPost();
        $this->_mockFlash();
        $this->Trait->getRequest()->expects($this->once())
                ->method('getData')
                ->with('reference')
                ->will($this->returnValue('not-found'));

        $this->Trait->Flash->expects($this->once())
                ->method('error')
                ->with('User not-found was not found');
        $this->Trait->expects($this->never())
                ->method('redirect')
                ->with(['action' => 'login']);
        $this->Trait->resendTokenValidation();
    }
}

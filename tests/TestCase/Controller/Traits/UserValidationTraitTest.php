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

use Cake\Event\Event;
use Cake\Http\Response;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

#[AllowMockObjectsWithoutExpectations]
class UserValidationTraitTest extends BaseTrait
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
        $this->traitMockMethods = ['dispatchEvent', 'redirect', 'getUsersTable', 'set'];
        $this->traitMockAddMethods = ['isStopped'];
        $this->mockDefaultEmail = true;
        parent::setUp();
    }

    /**
     * test
     *
     * @return void
     */
    public function testValidateHappyEmail(): void
    {
        $flash = $this->mockFlash();
        $user = $this->table->findByToken('token-3')->first();
        $this->assertFalse($user->active);
        $flash->expects('set')
                ->with('User account validated successfully', ['element' => 'success']);
        $this->Trait->expects($this->once())
                ->method('redirect')
                ->with($this->loginUrl())
                ->willReturn(new Response());
        $this->Trait->validate('email', 'token-3');
        $user = $this->table->findById($user->id)->first();
        $this->assertTrue($user->active);
    }

    /**
     * test
     *
     * @return void
     */
    public function testValidateHappyEmailWithAfterEmailTokenValidationEvent(): void
    {
        $event = new Event('event');
        $event->setResult([
            'action' => 'newAction',
        ]);
        $this->Trait->expects($this->once())
            ->method('dispatchEvent')
            ->willReturn($event);
        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with(['action' => 'newAction'])
            ->willReturn(new Response());
        $this->Trait->validate('email', 'token-3');
    }

    /**
     * test
     *
     * @return void
     */
    public function testValidateUserNotFound(): void
    {
        $flash = $this->mockFlash();
        $flash->expects('set')
                ->with('Invalid token or user account already validated', ['element' => 'error']);
        $this->Trait->expects($this->once())
                ->method('redirect')
                ->with($this->loginUrl())
                ->willReturn(new Response());
        $this->Trait->validate('email', 'not-found');
    }

    /**
     * test
     *
     * @return void
     */
    public function testValidateTokenExpired(): void
    {
        $flash = $this->mockFlash();
        $flash->expects('set')
                ->with('Token already expired', ['element' => 'error']);
        $this->Trait->expects($this->once())
                ->method('redirect')
                ->with($this->loginUrl())
                ->willReturn(new Response());
        $this->Trait->validate('email', '6614f65816754310a5f0553436dd89e9');
    }

    /**
     * test
     *
     * @return void
     */
    public function testValidateTokenExpiredWithOnExpiredEvent(): void
    {
        $event = new Event('event');
        $event->setResult([
            'action' => 'newAction',
        ]);
        $this->Trait->expects($this->once())
            ->method('dispatchEvent')
            ->willReturn($event);
        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with(['action' => 'newAction'])
            ->willReturn(new Response());
        $this->Trait->validate('email', '6614f65816754310a5f0553436dd89e9');
    }

    /**
     * test
     *
     * @return void
     */
    public function testValidateInvalidOp(): void
    {
        $flash = $this->mockFlash();
        $flash->expects('set')
                ->with('Invalid validation type', ['element' => 'error']);
        $this->Trait->expects($this->once())
                ->method('redirect')
                ->with($this->loginUrl())
                ->willReturn(new Response());
        $this->Trait->validate('invalid-op', '6614f65816754310a5f0553436dd89e9');
    }

    /**
     * test
     *
     * @return void
     */
    public function testValidateHappyPassword(): void
    {
        $this->mockRequestGet();
        $flash = $this->mockFlash();
        $user = $this->table->findByToken('token-4')->first();
        $this->assertTrue($user->active);
        $flash->expects('set')
                ->with('Reset password token was validated successfully', ['element' => 'success']);
        $this->Trait->expects($this->once())
                ->method('redirect')
                ->with(['action' => 'changePassword'])
                ->willReturn(new Response());
        $this->Trait->validate('password', 'token-4');
        $user = $this->table->findById($user->id)->first();
        $this->assertTrue($user->active);
    }

    /**
     * test
     *
     * @return void
     */
    public function testResendTokenValidationHappy(): void
    {
        $this->mockRequestPost();
        $flash = $this->mockFlash();
        $this->Trait->getRequest()->expects($this->once())
                ->method('getData')
                ->with('reference')
                ->willReturn('user-3');

        $flash->expects('set')
                ->with('Token has been reset successfully. Please check your email.', ['element' => 'success']);
        $this->Trait->expects($this->once())
                ->method('redirect')
                ->with($this->loginUrl())
                ->willReturn(new Response());
        $this->Trait->resendTokenValidation();
    }

    /**
     * test
     *
     * @return void
     */
    public function testResendTokenValidationWithAfterResendTokenValidationEvent(): void
    {
        $this->mockRequestPost();
        $this->mockFlash();
        $this->Trait->getRequest()->expects($this->once())
            ->method('getData')
            ->with('reference')
            ->willReturn('user-3');

        $event = new Event('event');
        $event->setResult([
            'action' => 'newAction',
        ]);
        $this->Trait->expects($this->once())
            ->method('dispatchEvent')
            ->willReturn($event);
        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with(['action' => 'newAction'])
            ->willReturn(new Response());

        $this->Trait->resendTokenValidation();
    }

    /**
     * test
     *
     * @return void
     */
    public function testResendTokenValidationAlreadyActive(): void
    {
        $this->mockRequestPost();
        $flash = $this->mockFlash();
        $this->Trait->getRequest()->expects($this->once())
                ->method('getData')
                ->with('reference')
                ->willReturn('user-4');

        $flash->expects('set')
                ->with('User user-4 is already active', ['element' => 'error']);
        $this->Trait->expects($this->never())
                ->method('redirect')
                ->with($this->loginUrl())
                ->willReturn(new Response());
        $this->Trait->resendTokenValidation();
    }

    /**
     * test
     *
     * @return void
     */
    public function testResendTokenValidationNotFound(): void
    {
        $this->mockRequestPost();
        $flash = $this->mockFlash();
        $this->Trait->getRequest()->expects($this->once())
                ->method('getData')
                ->with('reference')
                ->willReturn('not-found');

        $flash->expects('set')
                ->with('User not-found was not found', ['element' => 'error']);
        $this->Trait->expects($this->never())
                ->method('redirect')
                ->with($this->loginUrl())
                ->willReturn(new Response());
        $this->Trait->resendTokenValidation();
    }

    /**
     * Login redirect url.
     *
     * @return array
     */
    protected function loginUrl(): array
    {
        return [
            'action' => 'login',
            'prefix' => false,
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
        ];
    }
}

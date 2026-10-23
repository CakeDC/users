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

use Authentication\PasswordHasher\PasswordHasherFactory;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;

#[AllowMockObjectsWithoutExpectations]
class PasswordManagementTraitTest extends BaseTrait
{
    /**
     * @var \CakeDC\Users\Controller\UsersController
     */
    public $Trait;

    /**
     * setUp
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->traitClassName = 'CakeDC\Users\Controller\UsersController';
        $this->traitMockMethods = ['set', 'redirect', 'validate', 'log', 'dispatchEvent', 'getUsersTable'];
        $this->traitMockAddMethods = [];
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
    public function testChangePasswordHappy(): void
    {
        $this->assertEquals('12345', $this->table->get('00000000-0000-0000-0000-000000000001')->password);
        $this->mockRequestPost(['post', 'put']);
        $this->mockAuthLoggedIn();
        $flash = $this->mockFlash();
        $this->Trait->getRequest()->expects($this->once())
                ->method('getData')
                ->willReturn([
                    'password' => 'new',
                    'password_confirm' => 'new',
                ]);
        $this->Trait->expects($this->once())
                ->method('redirect')
                ->with(['prefix' => false, 'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'profile']);
        $flash->expects('set')
            ->with('Password has been changed successfully', ['element' => 'success']);
        $this->Trait->changePassword();
        $hasher = PasswordHasherFactory::build('Authentication.Default');
        $this->assertTrue($hasher->check('new', $this->table->get('00000000-0000-0000-0000-000000000001')->password));
    }

    /**
     * test
     *
     * @return void
     */
    public function testChangePasswordWithError(): void
    {
        $this->assertEquals('12345', $this->table->get('00000000-0000-0000-0000-000000000001')->password);
        $this->mockRequestPost(['post', 'put']);
        $this->mockAuthLoggedIn();
        $flash = $this->mockFlash();
        $this->Trait->getRequest()->expects($this->once())
                ->method('getData')
                ->willReturn([
                    'password' => 'new',
                    'password_confirm' => 'wrong_new',
                ]);
        $flash->expects('set')
            ->with('Password could not be changed', ['element' => 'error']);
        $this->Trait->expects($this->exactly(2))
            ->method('set')
            ->willReturn($this->Trait);
        $this->Trait->changePassword();
    }

    /**
     * test
     *
     * @return void
     */
    public function testChangePasswordWithAfterChangeEvent(): void
    {
        $this->assertEquals('12345', $this->table->get('00000000-0000-0000-0000-000000000001')->password);
        $this->mockRequestPost(['post', 'put']);
        $this->mockAuthLoggedIn();
        $this->mockFlash();
        $this->Trait->getRequest()->expects($this->once())
            ->method('getData')
            ->willReturn([
                'password' => 'new',
                'password_confirm' => 'new',
            ]);
        $event = new Event('event');
        $event->setResult([
            'action' => 'newAction',
        ]);
        $this->Trait->expects($this->once())
            ->method('dispatchEvent')
            ->willReturn($event);
        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with(['action' => 'newAction']);
        $this->Trait->changePassword();
        $hasher = PasswordHasherFactory::build('Authentication.Default');
        $this->assertTrue($hasher->check('new', $this->table->get('00000000-0000-0000-0000-000000000001')->password));
    }

    /**
     * test
     *
     * @return void
     */
    public function testChangePasswordWithSamePassword(): void
    {
        $this->assertEquals(
            '$2y$10$IPPgJNSfvATsMBLbv/2r8OtpyTBibyM1g5GDxD4PivW9qBRwRkRbC',
            $this->table->get('00000000-0000-0000-0000-000000000006')->password,
        );
        $this->mockRequestPost(['post', 'put']);
        $this->mockAuthLoggedIn(['id' => '00000000-0000-0000-0000-000000000006', 'password' => '$2y$10$IPPgJNSfvATsMBLbv/2r8OtpyTBibyM1g5GDxD4PivW9qBRwRkRbC']);
        $flash = $this->mockFlash();
        $this->Trait->getRequest()->expects($this->once())
            ->method('getData')
            ->willReturn([
                'current_password' => '12345',
                'password' => '12345',
                'password_confirm' => '12345',
            ]);
        $flash->expects('set')
            ->with('You cannot use the current password as the new one', ['element' => 'error']);
        $this->Trait->expects($this->exactly(2))
            ->method('set')
            ->willReturn($this->Trait);
        $this->Trait->changePassword();
    }

    /**
     * test
     *
     * @return void
     */
    public function testChangePasswordWithEmptyCurrentPassword(): void
    {
        $this->mockRequestPost(['post', 'put']);
        $this->mockAuthLoggedIn(['id' => '00000000-0000-0000-0000-000000000006', 'password' => '$2y$10$IPPgJNSfvATsMBLbv/2r8OtpyTBibyM1g5GDxD4PivW9qBRwRkRbC']);
        $flash = $this->mockFlash();
        $this->Trait->getRequest()->expects($this->once())
            ->method('getData')
            ->willReturn([
                'current_password' => '',
                'password' => '54321',
                'password_confirm' => '54321',
            ]);
        $flash->expects('set')
            ->with('Password could not be changed', ['element' => 'error']);
        $this->Trait->expects($this->exactly(2))
            ->method('set')
            ->willReturn($this->Trait);
        $this->Trait->changePassword();
    }

    /**
     * test
     *
     * @return void
     */
    public function testChangePasswordWithWrongCurrentPassword(): void
    {
        $this->assertEquals(
            '$2y$10$IPPgJNSfvATsMBLbv/2r8OtpyTBibyM1g5GDxD4PivW9qBRwRkRbC',
            $this->table->get('00000000-0000-0000-0000-000000000006')->password,
        );
        $this->mockRequestPost(['post', 'put']);
        $this->mockAuthLoggedIn(['id' => '00000000-0000-0000-0000-000000000006', 'password' => '$2y$10$IPPgJNSfvATsMBLbv/2r8OtpyTBibyM1g5GDxD4PivW9qBRwRkRbC']);
        $flash = $this->mockFlash();
        $this->Trait->getRequest()->expects($this->once())
            ->method('getData')
            ->willReturn([
                'current_password' => 'wrong-password',
                'password' => '12345',
                'password_confirm' => '12345',
            ]);
        $flash->expects('set')
            ->with('The current password does not match', ['element' => 'error']);
        $this->Trait->expects($this->exactly(2))
            ->method('set')
            ->willReturn($this->Trait);
        $this->Trait->changePassword();
    }

    /**
     * test
     *
     * @return void
     */
    public function testChangePasswordWithInvalidUser(): void
    {
        $this->mockRequestPost(['post', 'put']);
        $this->mockAuthLoggedIn(['id' => '12312312-0000-0000-0000-000000000002', 'password' => 'invalid-pass']);
        $flash = $this->mockFlash();
        $this->Trait->getRequest()->expects($this->once())
                ->method('getData')
                ->willReturn([
                    'password' => 'new',
                    'password_confirm' => 'new',
                ]);
        $flash->expects('set')
            ->with('User was not found', ['element' => 'error']);
        $this->Trait->expects($this->exactly(2))
            ->method('set')
            ->willReturn($this->Trait);
        $this->Trait->changePassword();
    }

    /**
     * test
     *
     * @return void
     */
    public function testChangePasswordGetLoggedIn(): void
    {
        $request = $this->getMockBuilder('Cake\Http\ServerRequest')
            ->onlyMethods(['is', 'referer', 'getData'])
            ->getMock();
        $this->Trait->setRequest($request);
        $this->Trait->getRequest()->expects($this->once())
            ->method('is')
            ->with(['post', 'put'])
            ->willReturn(false);
        $this->mockAuthLoggedIn();
        $this->Trait->expects($this->exactly(2))
                ->method('set')
                ->willReturnCallback(function ($param1, $param2 = null) {
                    if ($param1 === 'validatePassword') {
                        TestCase::assertEquals($param2, true);
                    }

                    return $this->Trait;
                });
        $this->Trait->changePassword();
    }

    /**
     * test
     *
     * @return void
     */
    public function testChangePasswordGetNotLoggedInInsideResetPasswordFlow(): void
    {
        $methods = ['is', 'referer', 'getData', 'getSession'];
        $request = $this->getMockBuilder('Cake\Http\ServerRequest')
            ->onlyMethods($methods)
            ->getMock();
        $this->Trait->setRequest($request);
        $this->Trait->getRequest()->expects($this->once())
            ->method('is')
            ->with(['post', 'put'])
            ->willReturn(false);

        $this->mockAuthentication(null, [], false);
        $this->mockFlash();
        $this->mockSession([
            Configure::read('Users.Key.Session.resetPasswordUserId') => '00000000-0000-0000-0000-000000000001',
        ]);
        $this->Trait->expects($this->exactly(2))
            ->method('set')
            ->willReturnCallback(function ($param1, $param2 = null) {
                if ($param1 === 'validatePassword') {
                    TestCase::assertEquals($param2, false);
                }

                return $this->Trait;
            });
        $this->Trait->changePassword();
    }

    /**
     * test
     *
     * @return void
     */
    public function testChangePasswordGetNotLoggedInOutsideResetPasswordFlow(): void
    {
        $this->mockRequestGet();
        $this->mockAuthentication(null, [], false);
        $flash = $this->mockFlash();
        $flash->expects('set')
            ->with('User was not found', ['element' => 'error']);
        $this->Trait->expects($this->once())
            ->method('redirect')
            ->willReturn(new Response());
        $this->Trait->changePassword();
    }

    /**
     * test
     *
     * @return void
     */
    public function testResetPassword(): void
    {
        $token = 'token';
        $this->Trait->expects($this->once())
                ->method('validate')
                ->with('password', $token);
        $this->Trait->resetPassword($token);
    }

    /**
     * test
     *
     * @return void
     */
    public function testRequestResetPasswordGet(): void
    {
        $this->assertEquals('ae93ddbe32664ce7927cf0c5c5a5e59d', $this->table->get('00000000-0000-0000-0000-000000000001')->token);
        $this->mockRequestGet();
        $this->mockFlash();
        $this->Trait->getRequest()->expects($this->never())
                ->method('getData');
        $this->Trait->expects($this->once())
                ->method('set')
                ->willReturn($this->Trait);
        $this->Trait->requestResetPassword();
    }

    /**
     * test
     *
     * @return void
     */
    public function testRequestPasswordHappy(): void
    {
        $this->assertEquals('6614f65816754310a5f0553436dd89e9', $this->table->get('00000000-0000-0000-0000-000000000002')->token);
        $this->mockRequestPost();
        $this->mockAuthLoggedIn();
        $flash = $this->mockFlash();
        $reference = 'user-2';
        $this->Trait->getRequest()->expects($this->once())
                ->method('getData')
                ->with('reference')
                ->willReturn($reference);
        $flash->expects('set')
            ->with('If the account is valid, the system will send an instructional email to the address on record.', ['element' => 'success']);
        $this->Trait->expects($this->once())
                ->method('redirect')
                ->with(['action' => 'login'])
                ->willReturn(new Response());
        $this->Trait->requestResetPassword();
        $this->assertNotEquals('xxx', $this->table->get('00000000-0000-0000-0000-000000000002')->token);
    }

    /**
     * test
     *
     * @return void
     */
    public function testRequestPasswordInvalidUser(): void
    {
        $this->mockRequestPost();
        $this->mockAuthLoggedIn(['id' => 'invalid-id', 'password' => 'invalid-pass']);
        $flash = $this->mockFlash();
        $reference = '12312312-0000-0000-0000-000000000002';
        $this->Trait->getRequest()->expects($this->once())
                ->method('getData')
                ->with('reference')
                ->willReturn($reference);
        $flash->expects('set')
            ->with('If the account is valid, the system will send an instructional email to the address on record.', ['element' => 'success']);

        $this->Trait->expects($this->never())
            ->method('redirect');
        $actual = $this->Trait->requestResetPassword();
        $this->assertNull($actual);
    }

    /**
     * test
     *
     * @return void
     */
    public function testRequestPasswordEmptyReference(): void
    {
        $this->mockRequestPost();
        $this->mockAuthLoggedIn(['id' => 'invalid-id', 'password' => 'invalid-pass']);
        $flash = $this->mockFlash();
        $reference = '';
        $this->Trait->getRequest()->expects($this->once())
                ->method('getData')
                ->with('reference')
                ->willReturn($reference);
        $flash->expects('set')
            ->with('There was an error please contact Administrator', ['element' => 'error']);

        $this->Trait->expects($this->never())
            ->method('redirect');
        $actual = $this->Trait->requestResetPassword();
        $this->assertNull($actual);
    }

    #[DataProvider('ensureUserActiveForResetPasswordFeature')]
    public function testEnsureUserActiveForResetPasswordFeature(bool $ensureActive): void
    {
        if ($ensureActive) {
            Configure::write('Users.Registration.ensureActive', true);
        }

        $this->assertEquals('ae93ddbe32664ce7927cf0c5c5a5e59d', $this->table->get('00000000-0000-0000-0000-000000000001')->token);
        $this->mockRequestPost();
        $flash = $this->mockFlash();
        $reference = 'user-1';
        $this->Trait->getRequest()->expects($this->once())
                ->method('getData')
                ->with('reference')
                ->willReturn($reference);
        $flash->expects('set')
            ->with('If the account is valid, the system will send an instructional email to the address on record.', ['element' => 'success']);
        $this->Trait->expects($this->once())
                ->method('set')
                ->willReturn($this->Trait);
        $this->Trait->requestResetPassword();
        $this->assertNotEquals('xxx', $this->table->get('00000000-0000-0000-0000-000000000001')->token);
    }

    public static function ensureUserActiveForResetPasswordFeature(): array
    {
        $ensureActive = true;
        $defaultBehavior = false;

        return [
            [$ensureActive],
            [$defaultBehavior],
        ];
    }

    #[DataProvider('ensureOneTimePasswordAuthenticatorResets')]
    public function testRequestGoogleAuthTokenResetWithValidUser(mixed $userId, mixed $entityId, string $method, string $msg): void
    {
        $this->mockRequestPost();
        $this->mockFlash();
        $response = new Response();
        $response = $response->withLocation('/');
        $this->Trait->expects($this->once())
            ->method('redirect')
            ->willReturn($response);

        $actual = $this->Trait->resetOneTimePasswordAuthenticator($entityId);
        $this->assertSame($response, $actual);
    }

    public static function ensureOneTimePasswordAuthenticatorResets(): array
    {
        $error = 'error';
        $success = 'success';
        $errorMsg = 'Could not reset Google Authenticator';
        $successMsg = 'Google Authenticator token was successfully reset';

        return [
            //is_superuser = true.
            ['00000000-0000-0000-0000-000000000003', null, $success, $successMsg],
            //is_superuser = true.
            ['00000000-0000-0000-0000-000000000001', null, $success, $successMsg],
            //is_superuser = false, and not his profile.
            ['00000000-0000-0000-0000-000000000004', '00000000-0000-0000-0000-000000000001', $error, $errorMsg],
            //is_superuser = false, editing own record.
            ['00000000-0000-0000-0000-000000000004', '00000000-0000-0000-0000-000000000004', $success, $successMsg],
            //is_superuser = false, and no entity-id given.
            ['00000000-0000-0000-0000-000000000004', null, $error, $errorMsg],
        ];
    }
}

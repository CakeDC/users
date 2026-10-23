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

use Cake\Http\Response;
use Cake\Http\ServerRequest;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

/**
 * @property \CakeDC\Users\Controller\Traits\ProfileTrait&\PHPUnit\Framework\MockObject\MockObject $Trait
 */
#[AllowMockObjectsWithoutExpectations]
class ProfileTraitTest extends BaseTrait
{
    /**
     * Fixtures
     *
     * @var array
     */
    protected array $fixtures = [
        'plugin.CakeDC/Users.Users',
        'plugin.CakeDC/Users.SocialAccounts',
    ];

    /**
     * setUp
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->traitClassName = 'CakeDC\Users\Controller\UsersController';
        $this->traitMockMethods = ['set', 'getUsersTable', 'redirect', 'validate'];
        parent::setUp();

        $this->Trait->setRequest(new ServerRequest());
    }

    /**
     * test
     *
     * @return void
     */
    public function testProfileGetNotLoggedInUserNotFound(): void
    {
        $userId = '00000000-0000-0000-0000-000000000000'; //not found
        $this->mockRequestGet();
        $this->mockAuthentication(null, [], false);
        $flash = $this->mockFlash();
        $flash->expects('set')
            ->with('User was not found', ['element' => 'error']);
        $this->Trait->expects($this->once())
            ->method('redirect')
            ->willReturn(new Response());
        $this->Trait->profile($userId);
    }

    /**
     * test
     *
     * @return void
     */
    public function testProfileGetLoggedInUserNotFound(): void
    {
        $userId = '00000000-0000-0000-0000-000000000000'; //not found
        $this->mockRequestGet();
        $this->mockAuthLoggedIn();
        $flash = $this->mockFlash();
        $flash->expects('set')
            ->with('User was not found', ['element' => 'error']);
        $this->Trait->expects($this->once())
            ->method('redirect')
            ->willReturn(new Response());
        $this->Trait->profile($userId);
    }

    /**
     * test
     *
     * @return void
     */
    public function testProfileGetNotLoggedInEmptyId(): void
    {
        $this->mockRequestGet();
        $this->mockAuthentication(null, [], false);
        $flash = $this->mockFlash();
        $flash->expects('set')
            ->with('Not authorized, please login first', ['element' => 'error']);
        $this->Trait->expects($this->once())
            ->method('redirect')
            ->willReturn(new Response());
        $this->Trait->profile();
    }

    /**
     * test
     *
     * @return void
     */
    public function testProfileGetLoggedInMyProfile(): void
    {
        $this->mockRequestGet();
        $this->mockAuthLoggedIn();
        $this->mockFlash();
        $this->Trait->expects($this->exactly(2))
            ->method('set')
            ->willReturnCallback(function ($param1, $param2 = null) {
                if ($param1 === 'avatarPlaceholder') {
                    BaseTrait::assertEquals('CakeDC/Users.avatar_placeholder.png', $param2);
                } elseif (is_array($param1)) {
                    BaseTrait::assertEquals('user-1', $param1['user']->username);
                }

                return $this->Trait;
            });
        $this->Trait->profile();
    }
}

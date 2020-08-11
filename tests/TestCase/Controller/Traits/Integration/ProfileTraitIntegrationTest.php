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

namespace CakeDC\Users\Test\TestCase\Controller\Traits\Integration;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

class ProfileTraitIntegrationTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.CakeDC/Users.Users',
        'plugin.CakeDC/Users.SocialAccounts',
    ];

    /**
     * Test login action with post request
     *
     * @return void
     */
    public function testProfile()
    {
        $user = TableRegistry::getTableLocator()
            ->get('CakeDC/Users.Users')
            ->get('00000000-0000-0000-0000-000000000001');

        $this->session(['Auth' => $user]);
        $this->get('/profile');
        $this->assertResponseOk();
        $this->assertResponseContains('<a href="/users/change-password">Change Password</a>');
        $this->assertResponseContains('<span class="full_name">first1 last1</span>');
        $this->assertResponseContains('user-1');
        $this->assertResponseContains('user-1@test.com');
        $this->assertResponseContains('Connected with Facebook');
        $this->assertResponseNotContains('Connect with Facebook');
        $this->assertResponseNotContains('/link-social/facebook');
        $this->assertResponseContains('Connected with Twitter');
        $this->assertResponseNotContains('Connect with Twitter');
        $this->assertResponseNotContains('/link-social/twitter');
        $this->assertResponseContains('<a href="/link-social/google" class="btn btn-social btn-google"><span class="fa fa-google"></span> Connect with Google</a>');
        $this->assertResponseNotContains('Connected with Google');
        $this->assertResponseContains('Social Accounts');
        $this->assertResponseContains('<td>Facebook</td>');
        $this->assertResponseContains('<td>Twitter</td>');
        $this->assertResponseNotContains('<td>Google</td>');
        $this->assertResponseNotContains('/link-social/amazon');
        $this->assertResponseNotContains('<td>Amazon</td>');
        $this->assertResponseContains('<span class="welcome">Welcome, <a href="/profile">first1</a></span>');

        $this->get('/users/change-password');
        $this->assertResponseOk();

        $this->enableSecurityToken();
        $this->post('/users/change-password', [
            'password' => '98765432102',
            'password_confirm' => '98765432102',
        ]);
        $this->assertRedirect('/profile');
        $this->assertFlashMessage('Password has been changed successfully');
    }
}

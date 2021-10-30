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

use Cake\Core\Configure;
use Cake\Event\EventManager;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use TestApp\Application;

class SimpleCrudTraitIntegrationTest extends TestCase
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
    public function testCrud()
    {
        $userId = '00000000-0000-0000-0000-000000000002';
        $user = TableRegistry::getTableLocator()
            ->get('CakeDC/Users.Users')
            ->get($userId);

        $this->session(['Auth' => $user]);
        $this->enableRetainFlashMessages();
        $this->get('/users/index');
        $this->assertResponseOk();
        $this->assertResponseContains('<span class="welcome">Welcome, <a href="/profile">user</a></span>');
        $this->assertResponseContains('<a href="/users/add">New Users</a>');
        $this->assertResponseContains('<td>user-1</td>');
        $this->assertResponseContains('<td>user-1@test.com</td>');
        $this->assertResponseContains('<td>first1</td>');
        $this->assertResponseContains('<td>last1</td>');
        $this->assertResponseContains('<a href="/users/view/00000000-0000-0000-0000-000000000001">View</a>');
        $this->assertResponseContains('<a href="/users/change-password/00000000-0000-0000-0000-000000000001">Change password</a>');
        $this->assertResponseContains('<a href="/users/edit/00000000-0000-0000-0000-000000000001">Edit</a>');
        $this->assertResponseContains('style="display:none;" method="post" action="/users/delete/00000000-0000-0000-0000-000000000001"');
        $this->assertResponseContains('>Delete<');

        $this->assertResponseContains('<td>user-6</td>');
        $this->assertResponseContains('<td>6@example.com</td>');
        $this->assertResponseContains('<td>first-user-6</td>');
        $this->assertResponseContains('<td>firts name 6</td>');
        $this->assertResponseContains('<a href="/users/view/00000000-0000-0000-0000-000000000006">View</a>');
        $this->assertResponseContains('<a href="/users/change-password/00000000-0000-0000-0000-000000000006">Change password</a>');
        $this->assertResponseContains('<a href="/users/edit/00000000-0000-0000-0000-000000000006">Edit</a>');
        $this->assertResponseContains('style="display:none;" method="post" action="/users/delete/00000000-0000-0000-0000-000000000006"');

        $this->get('/users/change-password/00000000-0000-0000-0000-000000000006');
        $this->assertFlashMessage('Changing another user\'s password is not allowed');
        $this->assertRedirect('/profile');

        EventManager::instance()->on(Application::EVENT_AFTER_PLUGIN_BOOTSTRAP, function () {
            Configure::write('Users.Superuser.allowedToChangePasswords', true);
        });
        $this->get('/users/change-password/00000000-0000-0000-0000-000000000005');
        $this->assertResponseOk();
        $this->assertResponseContains('<form method="post" accept-charset="utf-8" action="/users/change-password/00000000-0000-0000-0000-000000000005">');
        $this->assertResponseContains('<input type="password" name="password" required="required"');
        $this->assertResponseContains('<input type="password" name="password_confirm" required="required"');
        $this->assertResponseContains('<button type="submit">Submit</button>');

        $this->enableSecurityToken();
        $this->post('/users/change-password/00000000-0000-0000-0000-000000000005', [
            'password' => '123456',
            'password_confirm' => '123456',
        ]);
        $this->assertRedirect('/users/index');
        $this->assertFlashMessage('Password has been changed successfully');

        $this->get('/users/edit/00000000-0000-0000-0000-000000000006');
        $this->assertResponseContains('<input type="text" name="username" required="required');
        $this->assertResponseContains('id="username" aria-required="true" value="user-6"');
        $this->assertResponseContains('<input type="email" name="email" required="required');
        $this->assertResponseContains('id="email" aria-required="true" value="6@example.com"');
        $this->assertResponseContains('<input type="text" name="first_name" id="first-name" value="first-user-6');
        $this->assertResponseContains('<input type="text" name="last_name" id="last-name" value="firts name 6');
        $this->assertResponseContains('<label for="active"><input type="checkbox" name="active" value="1" id="active" checked="checked">Active</label>');
        $this->assertResponseContains('style="display:none;" method="post" action="/users/delete/00000000-0000-0000-0000-000000000006"');
        $this->assertResponseContains('<a href="/users/index">List Users</a>');

        $this->enableSecurityToken();
        $this->post('/users/edit/00000000-0000-0000-0000-000000000006', [
            'username' => 'my-new-username',
            'email' => 'crud.email992@example.com',
            'first_name' => 'Joe',
            'last_name' => 'Doe K',
        ]);
        $this->assertRedirect('/users/index');
        $this->assertFlashMessage('The User has been saved');
        $this->get('/users/view/00000000-0000-0000-0000-000000000006');
        $this->assertResponseOk();
        $this->assertResponseContains('>00000000-0000-0000-0000-000000000006<');
        $this->assertResponseContains('>my-new-username<');
        $this->assertResponseContains('>crud.email992@example.com<');
        $this->assertResponseContains('>Joe<');
        $this->assertResponseContains('>Doe K<');
        $this->assertResponseContains('>token-6<');
        $this->assertResponseContains('<a href="/users/edit/00000000-0000-0000-0000-000000000006">Edit User</a>');
        $this->assertResponseContains('<a href="/users/add">New User</a>');
        $this->assertResponseContains('<a href="/users/index">List Users</a>');
        $this->assertResponseContains('style="display:none;" method="post" action="/users/delete/00000000-0000-0000-0000-000000000006"');

        $this->post('/users/delete/00000000-0000-0000-0000-000000000006');
        $this->assertRedirect('/users/index');
        $this->assertFlashMessage('The User has been deleted');

        $this->get('/users/index');
        $this->assertResponseOk();
        $this->assertResponseNotContains('00000000-0000-0000-0000-000000000006');
        $this->assertResponseContains('00000000-0000-0000-0000-000000000001');
    }
}

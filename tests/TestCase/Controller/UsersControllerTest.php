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

namespace CakeDC\Users\Test\TestCase\Controller;

use Cake\Core\Configure;
use Cake\Http\ServerRequest;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

class UsersControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.CakeDC/Users.Users',
    ];

    /**
     * Test unathorize redirect using custom callable
     *
     * @return void
     */
    public function testUnauthorizedRedirectCustomCallable()
    {
        Configure::write('Auth.AuthorizationMiddleware.unauthorizedHandler.url', function ($request, $options) {
            $this->assertInstanceOf(ServerRequest::class, $request);
            $this->assertIsArray($options);

            return '/my/custom/url/';
        });
        $this->configRequest([
            'headers' => [
                'REFERER' => 'http://localhost/profile',
            ],
        ]);
        $this->get('/users/index');
        $this->assertRedirectContains('/my/custom/url/');
    }

    /**
     * Test unathorize redirect when user is NOT logged
     *
     * @return void
     */
    public function testUnauthorizedRedirectNotLogged()
    {
        $this->configRequest([
            'headers' => [
                'REFERER' => 'http://localhost/profile',
            ],
        ]);
        $this->get('/users/index');
        $this->assertRedirectContains('/login?redirect=http%3A%2F%2Flocalhost%2Fusers%2Findex');
    }

    /**
     * Test unathorize redirect when user is logged
     *
     * @return void
     */
    public function testUnauthorizedRedirectLogged()
    {
        $userId = '00000000-0000-0000-0000-000000000004';
        $user = TableRegistry::getTableLocator()
            ->get('CakeDC/Users.Users')
            ->get($userId);

        $this->session(['Auth' => $user]);
        $this->configRequest([
            'headers' => [
                'REFERER' => 'http://localhost/profile',
            ],
        ]);
        $this->get('/users/index');
        $this->assertRedirectContains('/profile');
    }
}

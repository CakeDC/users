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
namespace CakeDC\Users\Test\TestCase\Authenticator;

use Authentication\Authenticator\Result;
use Authentication\Identifier\IdentifierCollection;
use Cake\Core\Configure;
use Cake\Http\Client\Response;
use Cake\Http\ServerRequestFactory;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use CakeDC\Auth\Social\Mapper\Facebook;
use CakeDC\Users\Authenticator\SocialPendingEmailAuthenticator;
use CakeDC\Users\Model\Entity\User;

class SocialPendingEmailAuthenticatorTest extends TestCase
{
    public $fixtures = [
        'plugin.CakeDC/Users.Users',
        'plugin.CakeDC/Users.SocialAccounts',
    ];

    /**
     * @var \League\OAuth2\Client\Provider\Facebook
     */
    public $Provider;

    /**
     * @var \Cake\Http\ServerRequest
     */
    public $Request;

    /**
     * Setup the test case, backup the static object values so they can be restored.
     * Specifically backs up the contents of Configure and paths in App if they have
     * not already been backed up.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->Request = ServerRequestFactory::fromGlobals();
    }

    /**
     * testAuthenticate
     *
     * @return void
     */
    public function testAuthenticateInvalidUrl()
    {
        Router::connect('/users/validate-email/*', [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'socialEmail',
        ]);

        $user = $this->getUserData();
        $requestNoEmail = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/users/users/social-email-invalid'],
            [],
            []
        );
        $requestNoEmail->getSession()->write(Configure::read('Users.Key.Session.social'), $user);
        $Response = new Response();
        $identifiers = new IdentifierCollection([
            'CakeDC/Users.Social',
        ]);
        $Authenticator = new SocialPendingEmailAuthenticator($identifiers);
        $result = $Authenticator->authenticate($requestNoEmail, $Response);
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::FAILURE_OTHER, $result->getStatus());
    }

    /**
     * testAuthenticate
     *
     * @return void
     */
    public function testAuthenticateBaseFailed()
    {
        Router::connect('/users/social-email/*', [
             'plugin' => 'CakeDC/Users',
             'controller' => 'Users',
             'action' => 'socialEmail',
         ]);

        $user = $this->getUserData();
        $requestNoEmail = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/users/social-email', 'PHP_SELF' => ''],
            [],
            []
        );
        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/users/social-email', 'PHP_SELF' => ''],
            [],
            ['email' => 'testAuthenticateBaseFailed@example.com']
        );
        Configure::write('Users.Email.validate', false);
        $request->getSession()->write(Configure::read('Users.Key.Session.social'), $user);
        $requestNoEmail->getSession()->write(Configure::read('Users.Key.Session.social'), $user);
        $Response = new Response();
        $identifiers = new IdentifierCollection([
            'CakeDC/Users.Social',
        ]);
        $Authenticator = new SocialPendingEmailAuthenticator($identifiers);
        $result = $Authenticator->authenticate($requestNoEmail, $Response);
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::FAILURE_CREDENTIALS_MISSING, $result->getStatus());

        $Authenticator = new SocialPendingEmailAuthenticator($identifiers);
        $result = $Authenticator->authenticate($request, $Response);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::SUCCESS, $result->getStatus());
        $data = $result->getData();
        $this->assertInstanceOf(User::class, $data);
        $this->assertEquals('testAuthenticateBaseFailed@example.com', $data['email']);
    }

    /**
     * Get social user data for test
     *
     * @return mixed
     */
    protected function getUserData()
    {
        $Token = new \League\OAuth2\Client\Token\AccessToken([
            'access_token' => 'test-token',
            'expires' => 1490988496,
        ]);

        $data = [
            'token' => $Token,
            'id' => '1',
            'name' => 'Test User',
            'first_name' => 'Test',
            'last_name' => 'User',
            'hometown' => [
                'id' => '108226049197930',
                'name' => 'Madrid',
            ],
            'picture' => [
                'data' => [
                    'url' => 'https://scontent.xx.fbcdn.net/v/test.jpg',
                    'is_silhouette' => false,
                ],
            ],
            'cover' => [
                'source' => 'https://scontent.xx.fbcdn.net/v/test.jpg',
                'id' => '1',
            ],
            'gender' => 'male',
            'locale' => 'en_US',
            'link' => 'https://www.facebook.com/app_scoped_user_id/1/',
            'timezone' => -5,
            'age_range' => [
                'min' => 21,
            ],
            'bio' => 'I am the best test user in the world.',
            'picture_url' => 'https://scontent.xx.fbcdn.net/v/test.jpg',
            'is_silhouette' => false,
            'cover_photo_url' => 'https://scontent.xx.fbcdn.net/v/test.jpg',
        ];

        $mapper = new Facebook();
        $user = $mapper($data);
        $user['provider'] = 'facebook';
        $user['validated'] = true;

        return $user;
    }
}

<?php
/**
 * Copyright 2010 - 2018, Cake Development Corporation (https://www.cakedc.com)
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

use CakeDC\Users\Authenticator\SocialPendingEmailAuthenticator;
use CakeDC\Users\Model\Entity\User;
use CakeDC\Users\Social\Mapper\Facebook;
use CakeDC\Users\Social\MapUser;
use Cake\Core\Configure;
use Cake\Http\Client\Response;
use Cake\Http\ServerRequestFactory;
use Cake\TestSuite\TestCase;

class SocialPendingEmailAuthenticatorTest extends TestCase
{
    public $fixtures = [
        'plugin.CakeDC/Users.users',
        'plugin.CakeDC/Users.social_accounts'
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
    public function setUp()
    {
        parent::setUp();

        $this->Request = ServerRequestFactory::fromGlobals();
    }

    /**
     * testAuthenticate
     *
     * @return void
     */
    public function testAuthenticateBaseFailed()
    {
        $user = $this->getUserData();
        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/users/users/social-email'],
            [],
            ['email' => 'testAuthenticateBaseFailed@example.com', 'g-recaptcha-response' => 'BD-S2333-156465897897']
        );
        $requestNoEmail = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/users/users/social-email'],
            [],
            []
        );
        Configure::write('Users.Email.validate', false);
        $request->getSession()->write(Configure::read('Users.Key.Session.social'), $user);
        $requestNoEmail->getSession()->write(Configure::read('Users.Key.Session.social'), $user);
        $Response = new Response();
        $identifiers = new IdentifierCollection([
            'CakeDC/Users.Social'
        ]);
        $Authenticator = new SocialPendingEmailAuthenticator($identifiers);
        $result = $Authenticator->authenticate($requestNoEmail, $Response);
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::FAILURE_CREDENTIALS_MISSING, $result->getStatus());

        $Authenticator = $this->getMockBuilder(SocialPendingEmailAuthenticator::class)->setConstructorArgs([
            $identifiers
        ])->setMethods(['validateReCaptcha'])->getMock();

        $Authenticator->expects($this->once())
            ->method('validateReCaptcha')
            ->with(
                $this->equalTo('BD-S2333-156465897897')
            )
            ->will($this->returnValue(true));
        $result = $Authenticator->authenticate($request, $Response);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::SUCCESS, $result->getStatus());
        $data = $result->getData();
        $this->assertInstanceOf(User::class, $data);
        $this->assertEquals('testAuthenticateBaseFailed@example.com', $data['email']);
    }

    /**
     * testAuthenticate
     *
     * @return void
     */
    public function testAuthenticateInvalidRecaptcha()
    {
        $identifiers = new IdentifierCollection([
            'Authentication.Password'
        ]);

        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/users/users/social-email'],
            [],
            ['email' => 'testAuthenticateBaseFailed@example.com', 'g-recaptcha-response' => 'BD-S2333-156465897897']
        );
        $response = new Response();

        $Authenticator = $this->getMockBuilder(SocialPendingEmailAuthenticator::class)->setConstructorArgs([
            $identifiers
        ])->setMethods(['validateReCaptcha'])->getMock();

        $Authenticator->expects($this->once())
            ->method('validateReCaptcha')
            ->with(
                $this->equalTo('BD-S2333-156465897897')
            )
            ->will($this->returnValue(false));

        $user = $this->getUserData();
        Configure::write('Users.reCaptcha.login', true);
        Configure::write('Users.Email.validate', false);
        $request->getSession()->write(Configure::read('Users.Key.Session.social'), $user);
        $result = $Authenticator->authenticate($request, $response);
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(SocialPendingEmailAuthenticator::FAILURE_INVALID_RECAPTCHA, $result->getStatus());
        $this->assertNull($result->getData());
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
            'expires' => 1490988496
        ]);

        $data = [
            'token' => $Token,
            'id' => '1',
            'name' => 'Test User',
            'first_name' => 'Test',
            'last_name' => 'User',
            'hometown' => [
                'id' => '108226049197930',
                'name' => 'Madrid'
            ],
            'picture' => [
                'data' => [
                    'url' => 'https://scontent.xx.fbcdn.net/v/test.jpg',
                    'is_silhouette' => false
                ]
            ],
            'cover' => [
                'source' => 'https://scontent.xx.fbcdn.net/v/test.jpg',
                'id' => '1'
            ],
            'gender' => 'male',
            'locale' => 'en_US',
            'link' => 'https://www.facebook.com/app_scoped_user_id/1/',
            'timezone' => -5,
            'age_range' => [
                'min' => 21
            ],
            'bio' => 'I am the best test user in the world.',
            'picture_url' => 'https://scontent.xx.fbcdn.net/v/test.jpg',
            'is_silhouette' => false,
            'cover_photo_url' => 'https://scontent.xx.fbcdn.net/v/test.jpg'
        ];

        $user = (new Facebook())($data);
        $user['provider'] = 'facebook';
        $user['validated'] = true;

        return $user;
    }
}

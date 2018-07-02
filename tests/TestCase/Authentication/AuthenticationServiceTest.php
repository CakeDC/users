<?php

use Authentication\Authenticator\Result;
use Cake\Core\Configure;
use Cake\Http\Client\Response;
use Cake\Http\ServerRequestFactory;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CakeDC\Users\Authentication\AuthenticationService;
use CakeDC\Users\Authenticator\FormAuthenticator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthenticationServiceTest extends TestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.CakeDC/Users.users',
        'plugin.CakeDC/Users.social_accounts'
    ];

    /**
     * testAuthenticate
     *
     * @return void
     */
    public function testAuthenticate()
    {
        $Table = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $entity = $Table->get('00000000-0000-0000-0000-000000000001');
        $entity->password = 'password';
        $this->assertTrue((bool)$Table->save($entity));
        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/testpath'],
            [],
            ['username' => 'user-1', 'password' => 'password']
        );
        $response = new Response();

        $service = new AuthenticationService([
            'identifiers' => [
                'Authentication.Password'
            ],
            'authenticators' => [
                'Authentication.Session',
                'CakeDC/Users.Form'
            ]
        ]);

        $result = $service->authenticate($request, $response);
        $this->assertInstanceOf(Result::class, $result['result']);
        $this->assertInstanceOf(ServerRequestInterface::class, $result['request']);
        $this->assertInstanceOf(ResponseInterface::class, $result['response']);

        $this->assertTrue($result['result']->isValid());

        $result = $service->getAuthenticationProvider();
        $this->assertInstanceOf(FormAuthenticator::class, $result);

        $this->assertEquals(
            'user-1',
            $request->getAttribute('session')->read('Auth.username')
        );
        $this->assertEmpty($response->getHeaderLine('Location'));
        $this->assertNull($response->getStatusCode());
    }

    /**
     * testAuthenticate
     *
     * @return void
     */
    public function testAuthenticateShouldDoGoogleVerifyEnabled()
    {
        Configure::write('Users.GoogleAuthenticator.login', true);
        $Table = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $entity = $Table->get('00000000-0000-0000-0000-000000000001');
        $entity->password = 'password';
        $this->assertTrue((bool)$Table->save($entity));
        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/testpath'],
            [],
            ['username' => 'user-1', 'password' => 'password']
        );
        $response = new Response();

        $service = new AuthenticationService([
            'identifiers' => [
                'Authentication.Password' => []
            ],
            'authenticators' => [
                'Authentication.Session' => [
                    'skipGoogleVerify' => true,
                ],
                'CakeDC/Users.Form' => [
                    'skipGoogleVerify' => false,
                ]
            ]
        ]);

        $result = $service->authenticate($request, $response);
        $this->assertInstanceOf(Result::class, $result['result']);
        $this->assertInstanceOf(ServerRequestInterface::class, $result['request']);
        $this->assertInstanceOf(ResponseInterface::class, $result['response']);
        $this->assertFalse($result['result']->isValid());
        $this->assertEquals(AuthenticationService::NEED_GOOGLE_VERIFY, $result['result']->getStatus());
        $this->assertEquals('/users/users/verify', $result['response']->getHeaderLine('Location'));
        $this->assertEquals(302, $result['response']->getStatusCode());
        $this->assertNull($request->getAttribute('session')->read('Auth.username'));

    }

    /**
     * testAuthenticate
     *
     * @return void
     */
    public function testAuthenticateShouldDoGoogleVerifyDisabled()
    {
        Configure::write('Users.GoogleAuthenticator.login', false);
        $Table = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $entity = $Table->get('00000000-0000-0000-0000-000000000001');
        $entity->password = 'password';
        $this->assertTrue((bool)$Table->save($entity));
        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/testpath'],
            [],
            ['username' => 'user-1', 'password' => 'password']
        );
        $response = new Response();

        $service = new AuthenticationService([
            'identifiers' => [
                'Authentication.Password' => []
            ],
            'authenticators' => [
                'Authentication.Session' => [
                    'skipGoogleVerify' => true,
                ],
                'CakeDC/Users.Form' => [
                    'skipGoogleVerify' => false,
                ]
            ]
        ]);

        $result = $service->authenticate($request, $response);
        $this->assertInstanceOf(Result::class, $result['result']);
        $this->assertInstanceOf(ServerRequestInterface::class, $result['request']);
        $this->assertInstanceOf(ResponseInterface::class, $result['response']);

        $this->assertTrue($result['result']->isValid());

        $result = $service->getAuthenticationProvider();
        $this->assertInstanceOf(FormAuthenticator::class, $result);

        $this->assertEquals(
            'user-1',
            $request->getAttribute('session')->read('Auth.username')
        );
        $this->assertEmpty($response->getHeaderLine('Location'));
        $this->assertNull($response->getStatusCode());

    }
}

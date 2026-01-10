<?php

declare(strict_types=1);

namespace CakeDC\Users\Test\TestCase\Loader;

use Cake\Core\Configure;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use CakeDC\Users\Loader\AuthenticationServiceLoader;

/**
 * AuthenticationServiceLoader Test Case
 */
class AuthenticationServiceLoaderTest extends TestCase
{
    /**
     * @var \CakeDC\Users\Loader\AuthenticationServiceLoader
     */
    protected $Loader;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->Loader = new AuthenticationServiceLoader();
        Configure::write('Auth.Authenticators', [
            'Authentication.Session',
            'Authentication.Form',
        ]);
        Configure::write('Auth.Identifiers', [
            'Authentication.Password',
        ]);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->Loader);
        parent::tearDown();
    }

    /**
     * Test loadAuthenticationService method
     *
     * @return void
     */
    public function testInvoke(): void
    {
        $request = new ServerRequest();
        $loader = $this->Loader;
        $service = $loader($request);

        $this->assertInstanceOf(\Authentication\AuthenticationServiceInterface::class, $service);
        $this->assertNotEmpty($service->authenticators());
    }

    /**
     * Test loadAuthenticationService with custom config
     *
     * @return void
     */
    public function testInvokeWithCustomConfig(): void
    {
        Configure::write('Auth.Authenticators', [
            'Token' => [
                'className' => 'Authentication.Token',
                'queryParam' => 'token',
            ],
        ]);

        $request = new ServerRequest();
        $loader = $this->Loader;
        $service = $loader($request);

        $this->assertInstanceOf(\Authentication\AuthenticationServiceInterface::class, $service);
        $authenticator = $service->authenticators()->get('Token');
        $this->assertInstanceOf(\Authentication\Authenticator\TokenAuthenticator::class, $authenticator);
    }
}

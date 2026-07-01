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

namespace CakeDC\Users\Test\TestCase\Middleware;

use Cake\Http\Response;
use Cake\Http\ServerRequestFactory;
use Cake\TestSuite\TestCase;
use CakeDC\Users\Middleware\AjaxRedirectMiddleware;
use TestApp\Http\TestRequestHandler;

class AjaxRedirectMiddlewareTest extends TestCase
{
    protected function pluginRequest(string $url, array $headers = [])
    {
        $request = ServerRequestFactory::fromGlobals(['REQUEST_URI' => $url])
            ->withParam('plugin', 'CakeDC/Users');
        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        return $request;
    }

    protected function redirectHandler(string $location): TestRequestHandler
    {
        return new TestRequestHandler(function ($request) use ($location) {
            return (new Response())->withStatus(302)->withHeader('Location', $location);
        });
    }

    /**
     * Seed the request session with a flash message shaped exactly as
     * FlashComponent stores it (the `element` is the method name, e.g. `error`).
     */
    protected function withFlash($request, string $element, string $message)
    {
        $request->getSession()->write('Flash', [
            'auth' => [
                ['message' => $message, 'key' => 'auth', 'element' => $element, 'params' => []],
            ],
        ]);

        return $request;
    }

    public function testHtmxRedirectBecomesHxRedirectHeader(): void
    {
        $request = $this->pluginRequest('/login', ['HX-Request' => 'true']);
        $response = (new AjaxRedirectMiddleware())->process($request, $this->redirectHandler('/dashboard'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('/dashboard', $response->getHeaderLine('HX-Redirect'));
        $this->assertSame('', (string)$response->getBody());
    }

    public function testJsonRedirectBecomesJsonBody(): void
    {
        $request = $this->pluginRequest('/login', ['Accept' => 'application/json']);
        $response = (new AjaxRedirectMiddleware())->process($request, $this->redirectHandler('/dashboard'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('application/json', $response->getHeaderLine('Content-Type'));
        $body = json_decode((string)$response->getBody(), true);
        $this->assertTrue($body['success']);
        $this->assertSame('/dashboard', $body['redirect']);
    }

    public function testJsonRedirectWithErrorFlashReportsFailure(): void
    {
        // A 2FA verify failure sets an error flash, then redirects to login.
        // The middleware must NOT label that redirect success:true.
        $request = $this->pluginRequest('/verify', ['Accept' => 'application/json']);
        $this->withFlash($request, 'error', 'Verification code is invalid. Try again');

        $response = (new AjaxRedirectMiddleware())->process($request, $this->redirectHandler('/login'));

        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode((string)$response->getBody(), true);
        $this->assertFalse($body['success']);
        $this->assertSame('Verification code is invalid. Try again', $body['error']);
        $this->assertSame('error', $body['flash']['type']);
        $this->assertSame('/login', $body['redirect']);
    }

    public function testJsonRedirectWithSuccessFlashReportsSuccessAndIncludesFlash(): void
    {
        // A genuine login success sets a success/welcome flash, then redirects.
        $request = $this->pluginRequest('/login', ['Accept' => 'application/json']);
        $this->withFlash($request, 'success', 'Welcome, user-2');

        $response = (new AjaxRedirectMiddleware())->process($request, $this->redirectHandler('/dashboard'));

        $body = json_decode((string)$response->getBody(), true);
        $this->assertTrue($body['success']);
        $this->assertArrayNotHasKey('error', $body);
        $this->assertSame('success', $body['flash']['type']);
        $this->assertSame('Welcome, user-2', $body['flash']['message']);
    }

    public function testHtmxRedirectDoesNotConsumeFlash(): void
    {
        // HTMX performs a full-page redirect; the flash must survive so the
        // destination page can render it (the middleware must not drain it).
        $request = $this->pluginRequest('/verify', ['HX-Request' => 'true']);
        $this->withFlash($request, 'error', 'Verification code is invalid. Try again');

        $response = (new AjaxRedirectMiddleware())->process($request, $this->redirectHandler('/login'));

        $this->assertSame('/login', $response->getHeaderLine('HX-Redirect'));
        $this->assertNotEmpty($request->getSession()->read('Flash'));
    }

    public function testNonRedirectIsPassedThrough(): void
    {
        $request = $this->pluginRequest('/login', ['HX-Request' => 'true']);
        $original = (new Response())->withStatus(200);
        $handler = new TestRequestHandler(fn($req) => $original);

        $this->assertSame($original, (new AjaxRedirectMiddleware())->process($request, $handler));
    }

    public function testOutOfPluginScopeIsPassedThrough(): void
    {
        $request = ServerRequestFactory::fromGlobals(['REQUEST_URI' => '/articles'])
            ->withHeader('HX-Request', 'true');
        $response = (new AjaxRedirectMiddleware())->process($request, $this->redirectHandler('/x'));

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('', $response->getHeaderLine('HX-Redirect'));
    }

    public function testNonAjaxRedirectIsPassedThrough(): void
    {
        $request = $this->pluginRequest('/login');
        $response = (new AjaxRedirectMiddleware())->process($request, $this->redirectHandler('/dashboard'));

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/dashboard', $response->getHeaderLine('Location'));
    }

    public function testSetCookieHeadersArePreserved(): void
    {
        $request = $this->pluginRequest('/login', ['HX-Request' => 'true']);
        $handler = new TestRequestHandler(function ($req) {
            return (new Response())
                ->withStatus(302)
                ->withHeader('Location', '/dashboard')
                ->withHeader('Set-Cookie', 'remember_me=abc; Path=/');
        });
        $response = (new AjaxRedirectMiddleware())->process($request, $handler);

        $this->assertSame('/dashboard', $response->getHeaderLine('HX-Redirect'));
        $this->assertSame('remember_me=abc; Path=/', $response->getHeaderLine('Set-Cookie'));
    }
}

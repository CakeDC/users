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

namespace CakeDC\Users\Test\TestCase\Controller;

use Cake\Core\Configure;
use Cake\Event\EventManager;
use Cake\Http\ServerRequest;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use CakeDC\Users\Controller\UsersController;

class AjaxIntegrationTest extends TestCase
{
    use IntegrationTestTrait;

    protected array $fixtures = [
        'plugin.CakeDC/Users.Users',
        'plugin.CakeDC/Users.SocialAccounts',
    ];

    protected function enableAjax(): void
    {
        EventManager::instance()->on('TestApp.afterPluginBootstrap', function () {
            Configure::write('Users.Ajax.enabled', true);
        });
    }

    public function testFormProtectionLoadedForHtmlWhenAjaxEnabled(): void
    {
        Configure::write('Users.Ajax.enabled', true);
        $controller = new UsersController(new ServerRequest());

        $this->assertTrue($controller->components()->has('FormProtection'));
        $this->assertTrue($controller->components()->has('AjaxResponse'));
    }

    public function testFormProtectionSkippedForJsonWhenAjaxEnabled(): void
    {
        Configure::write('Users.Ajax.enabled', true);
        Configure::write('Users.Ajax.skipFormProtectionForJson', true);
        $request = (new ServerRequest())->withHeader('Accept', 'application/json');
        $controller = new UsersController($request);

        $this->assertFalse($controller->components()->has('FormProtection'));
        $this->assertTrue($controller->components()->has('AjaxResponse'));
    }

    public function testNothingLoadsWhenAjaxDisabled(): void
    {
        Configure::write('Users.Ajax.enabled', false);
        $controller = new UsersController(new ServerRequest());

        $this->assertTrue($controller->components()->has('FormProtection'));
        $this->assertFalse($controller->components()->has('AjaxResponse'));
    }

    public function testLoginSuccessRedirectBecomesJson(): void
    {
        $this->enableAjax();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->post('/login', ['username' => 'user-2', 'password' => '12345']);

        $this->assertResponseCode(200);
        $this->assertContentType('application/json');
        $body = json_decode((string)$this->_response->getBody(), true);
        $this->assertTrue($body['success']);
        $this->assertArrayHasKey('redirect', $body);
    }

    public function testLoginSuccessRedirectBecomesHxRedirect(): void
    {
        $this->enableAjax();
        $this->configRequest(['headers' => ['HX-Request' => 'true']]);
        $this->post('/login', ['username' => 'user-2', 'password' => '12345']);

        $this->assertResponseCode(200);
        // CakePHP's Location header is absolute (built from App.fullBaseUrl); the
        // middleware echoes it verbatim so allowed cross-host redirects via
        // Users.AllowedRedirectHosts survive (rather than being collapsed to a path).
        $this->assertHeader('HX-Redirect', 'http://example.com/pages/home');
    }

    public function testJsonLoginFailureReturns401(): void
    {
        $this->enableAjax();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->post('/login', ['username' => 'user-2', 'password' => 'wrong-password']);

        $this->assertResponseCode(401);
        $this->assertContentType('application/json');
        $body = json_decode((string)$this->_response->getBody(), true);
        $this->assertFalse($body['success']);
        $this->assertNotEmpty($body['error']);
    }

    public function testHtmxLoginReturnsFragmentNotFullPage(): void
    {
        $this->enableAjax();
        $this->configRequest(['headers' => ['HX-Request' => 'true']]);
        $this->get('/login');

        $this->assertResponseOk();
        // The form content is present...
        $this->assertResponseContains('action="/login"');
        // ...but the full-page skeleton (rendered by the default layout) is not.
        $this->assertResponseNotContains('<!DOCTYPE');
        $this->assertResponseNotContains('<html');
    }

    public function testNonHtmxLoginStillReturnsFullPage(): void
    {
        $this->enableAjax();
        $this->get('/login');

        $this->assertResponseOk();
        $this->assertResponseContains('action="/login"');
    }

    public function testJsonRegisterValidationErrorsReturn422(): void
    {
        $this->enableAjax();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        // Missing required fields -> validation errors.
        $this->post('/register', ['username' => '', 'email' => '', 'password' => '']);

        $this->assertResponseCode(422);
        $this->assertContentType('application/json');
        $body = json_decode((string)$this->_response->getBody(), true);
        $this->assertFalse($body['success']);
        $this->assertNotEmpty($body['errors']);
    }

    public function testJsonProfileExcludesSensitiveFields(): void
    {
        $this->enableAjax();
        // Authenticate as a known fixture user.
        $user = \Cake\ORM\TableRegistry::getTableLocator()
            ->get('CakeDC/Users.Users')
            ->get('00000000-0000-0000-0000-000000000002');
        $this->session(['Auth' => $user]);
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->get('/users/profile');

        $this->assertResponseOk();
        $this->assertContentType('application/json');
        $raw = (string)$this->_response->getBody();

        // Positive: the user IS serialized at the top level (no data envelope).
        $body = json_decode($raw, true);
        $this->assertArrayNotHasKey('data', $body);
        $this->assertArrayHasKey('user', $body);
        $this->assertNotEmpty($body['user']['username']);
        $this->assertSame('user-2', $body['user']['username']);

        // Negative: sensitive fields must be absent.
        $this->assertStringNotContainsString('"password"', $raw);
        $this->assertStringNotContainsString('"api_token"', $raw);
        $this->assertStringNotContainsString('"secret"', $raw);
        $this->assertStringNotContainsString('"token"', $raw);
    }

    public function testFormProtectionStillLoadedForJsonWhenSkipDisabled(): void
    {
        Configure::write('Users.Ajax.enabled', true);
        Configure::write('Users.Ajax.skipFormProtectionForJson', false);
        $request = (new ServerRequest())->withHeader('Accept', 'application/json');
        $controller = new UsersController($request);

        $this->assertTrue($controller->components()->has('FormProtection'));
    }

    public function testHtmxAttributesPresentOnLoginFormWhenEnabled(): void
    {
        $this->enableAjax();
        $this->configRequest(['headers' => ['HX-Request' => 'true']]);
        $this->get('/login');

        $this->assertResponseContains('hx-post="/login"');
        $this->assertResponseContains('hx-target="#ajax-login-container"');
    }

    public function testHtmxAttributesAbsentWhenDisabled(): void
    {
        // Ajax disabled (default): the form must be unchanged.
        $this->get('/login');

        $this->assertResponseOk();
        $this->assertResponseNotContains('hx-post');
    }

    public function testJsonLoginFailureFlashTypeIsError(): void
    {
        // The JSON `flash` envelope must classify a real auth-failure flash as
        // type "error" (the element name FlashComponent stores for ->error()).
        $this->enableAjax();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->post('/login', ['username' => 'user-2', 'password' => 'wrong-password']);

        $body = json_decode((string)$this->_response->getBody(), true);
        $this->assertSame('error', $body['flash']['type']);
        $this->assertNotEmpty($body['flash']['message']);
    }

    public function testJsonFailureRedirectIsNotReportedAsSuccess(): void
    {
        // A two-factor `verify` failure sets an error flash and redirects to
        // login. With 2FA disabled the same guard fires ("enable Google
        // Authenticator first") without any TOTP setup. The middleware must
        // report that redirect as success:false, not the old hardcoded true.
        $this->enableAjax();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->get('/users/verify');

        $this->assertResponseCode(200);
        $this->assertContentType('application/json');
        $body = json_decode((string)$this->_response->getBody(), true);
        $this->assertFalse($body['success']);
        $this->assertNotEmpty($body['error']);
        $this->assertSame('error', $body['flash']['type']);
    }
}

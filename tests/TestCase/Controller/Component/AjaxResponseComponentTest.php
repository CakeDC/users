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

namespace CakeDC\Users\Test\TestCase\Controller\Component;

use Cake\Controller\ComponentRegistry;
use Cake\Event\Event;
use Cake\Http\ServerRequest;
use Cake\ORM\Entity;
use Cake\TestSuite\TestCase;
use CakeDC\Users\Controller\Component\AjaxResponseComponent;
use CakeDC\Users\Controller\UsersController;

class AjaxResponseComponentTest extends TestCase
{
    protected function makeComponent(ServerRequest $request): array
    {
        $controller = new UsersController($request);
        $component = new AjaxResponseComponent(new ComponentRegistry($controller));

        return [$controller, $component];
    }

    public function testHtmxSetsAjaxLayoutAndFlag(): void
    {
        $request = (new ServerRequest())->withHeader('HX-Request', 'true');
        [$controller, $component] = $this->makeComponent($request);

        $component->beforeRender(new Event('Controller.beforeRender', $controller));

        $this->assertSame('ajax', $controller->viewBuilder()->getLayout());
        $this->assertTrue($controller->viewBuilder()->getVar('ajaxEnabled'));
        // An HTMX request re-renders only the fragment, so the outer wrapper is omitted.
        $this->assertTrue($controller->viewBuilder()->getVar('ajaxFragment'));
    }

    public function testNormalRequestKeepsHtmlViewAndMarksFullPage(): void
    {
        [$controller, $component] = $this->makeComponent(new ServerRequest());

        $component->beforeRender(new Event('Controller.beforeRender', $controller));

        // The component is only loaded while the feature is enabled, so the forms are
        // always HTMX-enhanced; a full page renders the wrapper (ajaxFragment false).
        $this->assertTrue($controller->viewBuilder()->getVar('ajaxEnabled'));
        $this->assertFalse($controller->viewBuilder()->getVar('ajaxFragment'));
        $this->assertNotSame('Json', $controller->viewBuilder()->getClassName());
    }

    public function testJsonSuccessSwitchesToJsonViewAndSerializesSuccessFlash(): void
    {
        $request = (new ServerRequest())->withHeader('Accept', 'application/json');
        [$controller, $component] = $this->makeComponent($request);
        $controller->set('user', new Entity(['username' => 'jdoe']));
        $controller->viewBuilder()->setOption('serialize', ['user']);

        $component->beforeRender(new Event('Controller.beforeRender', $controller));

        $this->assertSame('Json', $controller->viewBuilder()->getClassName());
        $this->assertTrue($controller->viewBuilder()->getVar('success'));
        $serialize = (array)$controller->viewBuilder()->getOption('serialize');
        $this->assertContains('success', $serialize);
        $this->assertContains('flash', $serialize);
        $this->assertContains('user', $serialize); // pre-seeded key survives the merge
    }

    public function testJsonValidationErrorsSet422AndSerializeErrors(): void
    {
        $request = (new ServerRequest())->withHeader('Accept', 'application/json');
        [$controller, $component] = $this->makeComponent($request);
        $user = new Entity(['username' => '']);
        $user->setError('username', ['_required' => 'This field is required']);
        $controller->set('user', $user);

        $component->beforeRender(new Event('Controller.beforeRender', $controller));

        $this->assertSame(422, $controller->getResponse()->getStatusCode());
        $this->assertFalse($controller->viewBuilder()->getVar('success'));
        $serialize = (array)$controller->viewBuilder()->getOption('serialize');
        $this->assertContains('errors', $serialize);
    }

    public function testJsonValidationErrorsDetectedOnNonUserEntityVar(): void
    {
        // Validation detection must not be hard-coded to the view var named 'user';
        // any serialized entity carrying errors should drive the 422 response.
        $request = (new ServerRequest())->withHeader('Accept', 'application/json');
        [$controller, $component] = $this->makeComponent($request);
        $entity = new Entity(['email' => '']);
        $entity->setError('email', ['_required' => 'This field is required']);
        $controller->set('profile', $entity);
        $controller->viewBuilder()->setOption('serialize', ['profile']);

        $component->beforeRender(new Event('Controller.beforeRender', $controller));

        $this->assertSame(422, $controller->getResponse()->getStatusCode());
        $this->assertFalse($controller->viewBuilder()->getVar('success'));
        $serialize = (array)$controller->viewBuilder()->getOption('serialize');
        $this->assertContains('errors', $serialize);
    }

    public function testAfterLoginFailureSets401ForJson(): void
    {
        $request = (new ServerRequest())->withHeader('Accept', 'application/json');
        [$controller, $component] = $this->makeComponent($request);

        $component->afterLoginFailure(new Event('Users.Authentication.afterLoginFailure', $controller, ['result' => null]));

        $this->assertSame(401, $controller->getResponse()->getStatusCode());
    }

    public function testAfterLoginFailureIgnoresNonAjax(): void
    {
        [$controller, $component] = $this->makeComponent(new ServerRequest());

        $component->afterLoginFailure(new Event('Users.Authentication.afterLoginFailure', $controller, ['result' => null]));

        $this->assertSame(200, $controller->getResponse()->getStatusCode());
    }

    public function testJsonLoginFailureRendersErrorBodyWith401(): void
    {
        $request = (new ServerRequest())->withHeader('Accept', 'application/json');
        [$controller, $component] = $this->makeComponent($request);

        $component->afterLoginFailure(new Event('Users.Authentication.afterLoginFailure', $controller, ['result' => null]));
        $component->beforeRender(new Event('Controller.beforeRender', $controller));

        $this->assertSame(401, $controller->getResponse()->getStatusCode());
        $this->assertSame('Json', $controller->viewBuilder()->getClassName());
        $this->assertFalse($controller->viewBuilder()->getVar('success'));
        $this->assertNotEmpty($controller->viewBuilder()->getVar('error'));
        $serialize = (array)$controller->viewBuilder()->getOption('serialize');
        $this->assertContains('error', $serialize);
    }
}

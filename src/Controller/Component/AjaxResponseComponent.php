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

namespace CakeDC\Users\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\Controller;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use CakeDC\Users\Utility\AjaxFlash;

/**
 * Adds render-time JSON and HTMX behavior to the Users plugin controllers.
 *
 * Loaded only when `Users.Ajax.enabled` is true (see AppController::initialize()),
 * so it never runs for installations that have not opted in.
 */
class AjaxResponseComponent extends Component
{
    /**
     * Set in afterLoginFailure() so beforeRender() can render a 401 auth-error body.
     *
     * @var bool
     */
    protected bool $loginFailed = false;

    /**
     * @inheritDoc
     */
    public function implementedEvents(): array
    {
        return [
            'Controller.beforeRender' => 'beforeRender',
            'Users.Authentication.afterLoginFailure' => 'afterLoginFailure',
        ];
    }

    /**
     * Is the current request an HTMX request?
     *
     * @return bool
     */
    protected function isHtmx(): bool
    {
        return $this->getController()->getRequest()->hasHeader('HX-Request');
    }

    /**
     * Does the current request negotiate JSON?
     *
     * @return bool
     */
    protected function isJson(): bool
    {
        return $this->getController()->getRequest()->is('json');
    }

    /**
     * Flag a JSON login failure so beforeRender() can shape a 401 response.
     * (HTMX login failures re-render the form fragment, so they are not flagged here.)
     *
     * @param \Cake\Event\EventInterface $event The afterLoginFailure event.
     * @return void
     */
    public function afterLoginFailure(EventInterface $event): void
    {
        if (!$this->isJson()) {
            return;
        }
        $this->loginFailed = true;
        $controller = $this->getController();
        $controller->setResponse($controller->getResponse()->withStatus(401));
    }

    /**
     * Apply HTMX layout / JSON view negotiation just before rendering.
     *
     * @param \Cake\Event\EventInterface $event The beforeRender event.
     * @return void
     */
    public function beforeRender(EventInterface $event): void
    {
        $controller = $this->getController();
        $controller->set('ajaxEnabled', $this->isHtmx());

        if ($this->isHtmx()) {
            $controller->viewBuilder()->setLayout('ajax');

            return;
        }

        if ($this->isJson()) {
            $this->renderJson($controller);
        }
    }

    /**
     * Switch the view to JSON and assemble the response envelope.
     *
     * @param \Cake\Controller\Controller $controller The controller being rendered.
     * @return void
     */
    protected function renderJson(Controller $controller): void
    {
        $builder = $controller->viewBuilder();
        $builder->setClassName('Json');

        $flash = AjaxFlash::consume($controller->getRequest()->getSession());
        $controller->set('flash', $flash);

        if ($this->loginFailed) {
            $controller->set('success', false);
            $controller->set('error', $flash['message'] ?? __d('cake_d_c/users', 'Authentication failed'));
            $builder->setOption('serialize', ['success', 'error', 'flash']);

            return;
        }

        $errorEntity = $this->findEntityWithErrors($builder->getVars());
        if ($errorEntity !== null) {
            $controller->set('success', false);
            $controller->set('errors', $errorEntity->getErrors());
            $controller->setResponse($controller->getResponse()->withStatus(422));
            $builder->setOption('serialize', ['success', 'errors', 'flash']);

            return;
        }

        $controller->set('success', true);
        $existing = (array)$builder->getOption('serialize');
        $builder->setOption('serialize', array_values(array_unique(array_merge($existing, ['success', 'flash']))));
    }

    /**
     * Find the first view var that is an entity carrying validation errors.
     *
     * Keyed off "has errors" rather than a fixed var name so any action's entity
     * (register's `user`, a profile form, etc.) drives the 422 response.
     *
     * @param array $vars The view vars set on the controller.
     * @return \Cake\Datasource\EntityInterface|null The entity with errors, or null.
     */
    protected function findEntityWithErrors(array $vars): ?EntityInterface
    {
        foreach ($vars as $value) {
            if ($value instanceof EntityInterface && $value->hasErrors()) {
                return $value;
            }
        }

        return null;
    }
}

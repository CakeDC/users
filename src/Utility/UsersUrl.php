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

namespace CakeDC\Users\Utility;

use Cake\Core\Configure;
use Cake\Http\ServerRequest;

class UsersUrl
{
    /**
     * Check if users url uses a custom controller.
     *
     * @return bool
     */
    public static function isCustom(): bool
    {
        $controller = Configure::read('Users.controller', 'CakeDC/Users.Users');

        return $controller !== 'CakeDC/Users.Users';
    }

    /**
     * Get an user action url
     *
     * @param string|null $action user action
     * @param array $extra extra url attributes
     * @return array
     */
    public static function actionUrl(?string $action = null, array $extra = []): array
    {
        $params = static::actionParams($action);
        $params['prefix'] = $params['prefix'] ?: false;
        $params['plugin'] = $params['plugin'] ?: false;

        return $params + $extra;
    }

    /**
     * Get an user action route. This should not be used for links like HtmlHelper::link
     *
     * @param string|null $action user action
     * @return array
     */
    public static function actionRouteParams(?string $action = null): array
    {
        return array_filter(static::actionParams($action));
    }

    /**
     * Get an user action route. This should not be used for links like HtmlHelper::link
     *
     * @param string|null $action user action
     * @return array
     */
    public static function actionParams(?string $action = null): array
    {
        $prefix = null;
        $controller = Configure::read('Users.controller', 'CakeDC/Users.Users');
        [$plugin, $controller] = pluginSplit($controller);
        $parts = explode('/', $controller);
        if (isset($parts[1])) {
            $controller = $parts[1];
            $prefix = $parts[0];
        }

        return ['prefix' => $prefix, 'plugin' => $plugin, 'controller' => $controller, 'action' => $action];
    }

    /**
     * Check if the action is the one from a request
     *
     * @param string $action users action
     * @param \Cake\Http\ServerRequest $request the request
     * @return bool
     */
    public static function checkActionOnRequest(string $action, ServerRequest $request): bool
    {
        $route = static::actionParams($action);
        foreach ($route as $param => $value) {
            if ($request->getParam($param, null) !== $value) {
                return false;
            }
        }

        return true;
    }

    /**
     * Setup needed config urls but without overwriting
     *
     * @return void
     */
    public static function setupConfigUrls(): void
    {
        $urls = self::getDefaultConfigUrls();
        foreach ($urls as $configKey => $url) {
            if (!Configure::check($configKey)) {
                Configure::write($configKey, $url);
            }
        }
    }

    /**
     * Get a list of default config urls using static::actionUrl method for users url.
     *
     * @return array
     */
    private static function getDefaultConfigUrls(): array
    {
        $loginAction = static::actionUrl('login');

        return [
            'Users.Profile.route' => static::actionUrl('profile'),
            'OneTimePasswordAuthenticator.verifyAction' => static::actionUrl('verify'),
            'Webauthn2fa.startAction' => static::actionUrl('webauthn2fa'),
            'Auth.AuthenticationComponent.loginAction' => $loginAction,
            'Auth.AuthenticationComponent.logoutRedirect' => $loginAction,
            'Auth.Authenticators.Form.loginUrl' => $loginAction,
            'Auth.Authenticators.Cookie.loginUrl' => $loginAction,
            'Auth.Authenticators.SocialPendingEmail.loginUrl' => $loginAction,
            'Auth.AuthorizationMiddleware.unauthorizedHandler.url' => $loginAction,
            'OAuth.path' => static::actionParams('socialLogin'),
        ];
    }
}

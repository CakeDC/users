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

namespace CakeDC\Users\Utility;

use Cake\Core\Configure;
use Cake\Http\ServerRequest;

class UsersUrl
{
    /**
     * Get an user action url
     *
     * @param string $action user action
     * @param array $extra extra url attributes
     *
     * @return array
     */
    public function actionUrl($action, $extra = [])
    {
        $prefix = false;
        $controller = Configure::read('Users.controller', 'CakeDC/Users.Users');
        [$plugin, $controller] = pluginSplit($controller);
        $plugin = $plugin ? $plugin : false;

        return compact('prefix', 'plugin', 'controller', 'action') + $extra;
    }

    /**
     * Check if the action is the one from a request
     *
     * @param string $action users action
     * @param \Cake\Http\ServerRequest $request the request
     *
     * @return bool
     */
    public function checkActionOnRequest($action, ServerRequest $request)
    {
        $url = $this->actionUrl($action);
        foreach ($url as $param => $value) {
            $actual = $request->getParam($param) ?? false;
            if ($actual !== $value) {
                return false;
            }
        }

        return true;
    }
}

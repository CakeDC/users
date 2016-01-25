<?php
/**
 * Copyright 2010 - 2015, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2015, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Auth;

use Cake\Auth\BaseAuthorize;
use Cake\Controller\ComponentRegistry;
use Cake\Core\Configure;
use Cake\Core\Exception\Exception;
use Cake\Log\LogTrait;
use Cake\Network\Request;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use Psr\Log\LogLevel;

/**
 * Simple Rbac Authorize
 *
 * Matches current plugin/controller/action against defined permissions in permissions.php file
 */
class SimpleRbacAuthorize extends BaseAuthorize
{
    use LogTrait;

    protected $_defaultConfig = [
        //autoload permissions.php
        'autoload_config' => 'permissions',
        //role field in the Users table
        'role_field' => 'role',
        //default role, used in new users registered and also as role matcher when no role is available
        'default_role' => 'user',
        /*
         * This is a quick roles-permissions implementation
         * Rules are evaluated top-down, first matching rule will apply
         * Each line define
         *      [
         *          'role' => 'admin',
         *          'plugin', (optional, default = null)
         *          'prefix', (optional, default = null)
         *          'controller',
         *          'action',
         *          'allowed' (optional, default = true)
         *      ]
         * You could use '*' to match anything
         * You could use [] to match an array of options, example 'role' => ['adm1', 'adm2']
         * You could use a callback in your 'allowed' to process complex authentication, like
         *   - ownership
         *   - permissions stored in your database
         *   - permission based on an external service API call
         * Example ownership callback, to allow users to edit their own Posts:
         *
         * 'allowed' => function (array $user, $role, Request $request) {
                $postId = Hash::get($request->params, 'pass.0');
                $post = TableRegistry::get('Posts')->get($postId);
                $userId = Hash::get($user, 'id');
                if (!empty($post->user_id) && !empty($userId)) {
                    return $post->user_id === $userId;
                }
                return false;
            }
         *
         * Suggestion: put your rules into a specific config file
         */
        'permissions' => [],
    ];

    /**
     * Default permissions to be loaded if no provided permissions
     *
     * @var array
     */
    protected $_defaultPermissions = [
        //admin role allowed to use CakeDC\Users plugin actions
        [
            'role' => 'admin',
            'plugin' => '*',
            'controller' => '*',
            'action' => '*',
        ],
        //specific actions allowed for the user role in Users plugin
        [
            'role' => 'user',
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => ['profile', 'logout'],
        ],
        //all roles allowed to Pages/display
        [
            'role' => '*',
            'plugin' => null,
            'controller' => ['Pages'],
            'action' => ['display'],
        ],
    ];

    /**
     * Autoload permission configuration
     * @param ComponentRegistry $registry component registry
     * @param array $config config
     */
    public function __construct(ComponentRegistry $registry, array $config = [])
    {
        parent::__construct($registry, $config);
        $autoload = $this->config('autoload_config');
        if ($autoload) {
            $loadedPermissions = $this->_loadPermissions($autoload, 'default');
            $this->config('permissions', $loadedPermissions);
        }
    }

    /**
     * Load config and retrieve permissions
     * If the configuration file does not exist, or the permissions key not present, return defaultPermissions
     * To be mocked
     *
     * @param string $key name of the configuration file to read permissions from
     * @return array permissions
     */
    protected function _loadPermissions($key)
    {
        try {
            Configure::load($key, 'default');
            $permissions = Configure::read('Users.SimpleRbac.permissions');
        } catch (Exception $ex) {
            $msg = __d('Users', 'Missing configuration file: "config/{0}.php". Using default permissions', $key);
            $this->log($msg, LogLevel::WARNING);
        }

        if (empty($permissions)) {
            return $this->_defaultPermissions;
        }

        return $permissions;
    }

    /**
     * Match the current plugin/controller/action against loaded permissions
     * Set a default role if no role is provided
     *
     * @param array $user user data
     * @param Request $request request
     * @return bool
     */
    public function authorize($user, Request $request)
    {
        $roleField = $this->config('role_field');
        $role = $this->config('default_role');
        if (Hash::check($user, $roleField)) {
            $role = Hash::get($user, $roleField);
        }

        $allowed = $this->_checkRules($user, $role, $request);
        return $allowed;
    }

    /**
     * Match against permissions, return if matched
     * Permissions are processed based on the 'permissions' config values
     *
     * @param array $user current user array
     * @param string $role effective role for the current user
     * @param Request $request request
     * @return bool true if there is a match in permissisons
     */
    protected function _checkRules(array $user, $role, Request $request)
    {
        $permissions = $this->config('permissions');
        foreach ($permissions as $permission) {
            $allowed = $this->_matchRule($permission, $user, $role, $request);
            if ($allowed !== null) {
                return $allowed;
            }
        }

        return false;
    }

    /**
     * Match the rule for current permission
     *
     * @param array $permission configuration
     * @param array $user current user
     * @param string $role effective user role
     * @param Request $request request
     * @return bool if rule matched, null if rule not matched
     */
    protected function _matchRule($permission, $user, $role, $request)
    {
        $plugin = $request->plugin;
        $controller = $request->controller;
        $action = $request->action;
        $prefix = null;
        if (!empty($request->params['prefix'])) {
            $prefix = $request->params['prefix'];
        }
        if ($this->_matchOrAsterisk($permission, 'role', $role) &&
                $this->_matchOrAsterisk($permission, 'prefix', $prefix, true) &&
                $this->_matchOrAsterisk($permission, 'plugin', $plugin, true) &&
                $this->_matchOrAsterisk($permission, 'controller', $controller) &&
                $this->_matchOrAsterisk($permission, 'action', $action)) {
            $allowed = Hash::get($permission, 'allowed');
            if ($allowed === null) {
                //allowed will be true by default
                return true;
            } elseif (is_callable($allowed)) {
                return (bool)call_user_func($allowed, $user, $role, $request);
            } else {
                return (bool)$allowed;
            }
        }

        return null;
    }

    /**
     * Check if rule matched or '*' present in rule matching anything
     *
     * @param string $permission permission configuration
     * @param string $key key to retrieve and check in permissions configuration
     * @param string $value value to check with (coming from the request) We'll check the DASHERIZED value too
     * @param bool $allowEmpty true if we allow
     * @return bool
     */
    protected function _matchOrAsterisk($permission, $key, $value, $allowEmpty = false)
    {
        $possibleValues = (array)Hash::get($permission, $key);
        if ($allowEmpty && empty($possibleValues) && $value === null) {
            return true;
        }
        if (Hash::get($permission, $key) === '*' ||
                in_array($value, $possibleValues) ||
                in_array(Inflector::camelize($value, '-'), $possibleValues)) {
            return true;
        }

        return false;
    }
}

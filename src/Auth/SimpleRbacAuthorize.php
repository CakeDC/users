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

use CakeDC\Users\Auth\Rules\Rule;
use Cake\Auth\BaseAuthorize;
use Cake\Controller\ComponentRegistry;
use Cake\Core\Configure;
use Cake\Core\Exception\Exception;
use Cake\Log\LogTrait;
use Cake\Http\ServerRequest;
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
         *          'extension', (optional, default = null)
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
         * You could use an instance of the \CakeDC\Users\Auth\Rules\Rule interface to reuse your custom rules
         *
         * Examples:
         * 1. Callback to allow users editing their own Posts:
         *
         * 'allowed' => function (array $user, $role, Request $request) {
         *       $postId = Hash::get($request->params, 'pass.0');
         *       $post = TableRegistry::get('Posts')->get($postId);
         *       $userId = Hash::get($user, 'id');
         *       if (!empty($post->user_id) && !empty($userId)) {
         *           return $post->user_id === $userId;
         *       }
         *       return false;
         *   }
         * 2. Using the Owner Rule
         * 'allowed' => new Owner() //will pick by default the post id from the first pass param
         *
         * Check the Owner Rule docs for more details
         *
         *
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
        $autoload = $this->getConfig('autoload_config');
        if ($autoload) {
            $loadedPermissions = $this->_loadPermissions($autoload);
            $this->setConfig('permissions', $loadedPermissions);
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
            $msg = __d('CakeDC/Users', 'Missing configuration file: "config/{0}.php". Using default permissions', $key);
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
     * @param \Cake\Http\ServerRequest $request request
     * @return bool
     */
    public function authorize($user, ServerRequest $request)
    {
        $roleField = $this->getConfig('role_field');
        $role = $this->getConfig('default_role');
        if (Hash::check($user, $roleField)) {
            $role = Hash::get($user, $roleField);
        }

        $allowed = $this->_checkPermissions($user, $role, $request);

        return $allowed;
    }

    /**
     * Match against permissions, return if matched
     * Permissions are processed based on the 'permissions' config values
     *
     * @param array $user current user array
     * @param string $role effective role for the current user
     * @param \Cake\Http\ServerRequest $request request
     * @return bool true if there is a match in permissions
     */
    protected function _checkPermissions(array $user, $role, ServerRequest $request)
    {
        $permissions = $this->getConfig('permissions');
        foreach ($permissions as $permission) {
            $allowed = $this->_matchPermission($permission, $user, $role, $request);
            if ($allowed !== null) {
                return $allowed;
            }
        }

        return false;
    }

    /**
     * Match the rule for current permission
     *
     * @param array $permission The permission configuration
     * @param array $user Current user data
     * @param string $role Effective user's role
     * @param \Cake\Http\ServerRequest $request Current request
     *
     * @return null|bool Null if permission is discarded, boolean if a final result is produced
     */
    protected function _matchPermission(array $permission, array $user, $role, ServerRequest $request)
    {
        $issetController = isset($permission['controller']) || isset($permission['*controller']);
        $issetAction = isset($permission['action']) || isset($permission['*action']);
        $issetUser = isset($permission['user']) || isset($permission['*user']);

        if (!$issetController || !$issetAction) {
            $this->log(
                __d('CakeDC/Users', "Cannot evaluate permission when 'controller' and/or 'action' keys are absent"),
                LogLevel::DEBUG
            );

            return false;
        }
        if ($issetUser) {
            $this->log(
                __d('CakeDC/Users', "Permission key 'user' is illegal, cannot evaluate the permission"),
                LogLevel::DEBUG
            );

            return false;
        }

        $permission += ['allowed' => true];
        $userArr = ['user' => $user];
        $reserved = [
            'prefix' => $request->getParams('prefix'),
            'plugin' => $request->getParam('plugin'),
            'extension' => $request->getParam('_ext'),
            'controller' => $request->getParam('controller'),
            'action' => $request->getParam('action'),
            'role' => $role
        ];

        foreach ($permission as $key => $value) {
            $inverse = $this->_startsWith($key, '*');
            if ($inverse) {
                $key = ltrim($key, '*');
            }

            if (is_callable($value)) {
                $return = (bool)call_user_func($value, $user, $role, $request);
            } elseif ($value instanceof Rule) {
                $return = (bool)$value->allowed($user, $role, $request);
            } elseif ($key === 'allowed') {
                $return = (bool)$value;
            } elseif (array_key_exists($key, $reserved)) {
                $return = $this->_matchOrAsterisk($value, $reserved[$key], true);
            } else {
                if (!$this->_startsWith($key, 'user.')) {
                    $key = 'user.' . $key;
                }

                $return = $this->_matchOrAsterisk($value, Hash::get($userArr, $key));
            }

            if ($inverse) {
                $return = !$return;
            }
            if ($key === 'allowed') {
                return $return;
            }
            if (!$return) {
                break;
            }
        }

        return null;
    }

    /**
     * Check if rule matched or '*' present in rule matching anything
     *
     * @param string|array $possibleValues Values that are accepted (from permission config)
     * @param string|mixed|null $value Value to check with. We'll check the DASHERIZED value too
     * @param bool $allowEmpty If true and $value is null, the rule will pass
     *
     * @return bool
     */
    protected function _matchOrAsterisk($possibleValues, $value, $allowEmpty = false)
    {
        $possibleArray = (array)$possibleValues;

        if ($allowEmpty && empty($possibleArray) && $value === null) {
            return true;
        }

        if ($possibleValues === '*' ||
            in_array($value, $possibleArray) ||
            in_array(Inflector::camelize($value, '-'), $possibleArray)
        ) {
            return true;
        }

        return false;
    }

    /**
     * Checks if $haystack begins with $needle
     *
     * @see http://stackoverflow.com/a/7168986/2588539
     *
     * @param string $haystack The whole string
     * @param string $needle The beginning to check
     *
     * @return bool
     */
    protected function _startsWith($haystack, $needle)
    {
        return substr($haystack, 0, strlen($needle)) === $needle;
    }
}

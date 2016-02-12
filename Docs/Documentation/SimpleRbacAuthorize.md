SimpleRbacAuthorize
=============

Setup
---------------

SimpleRbacAuthorize is configured by default, but you can customize the way it works easily

Example, in you AppController, you can configure the way the SimpleRbac works by setting the options:

```php
$config['Auth']['authorize']['Users.SimpleRbac'] = [
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
         *          'controller',
         *          'action',
         *          'allowed' (optional, default = true)
         *      ]
         * You could use '*' to match anything
         * Suggestion: put your rules into a specific config file
         */
        'permissions' => [], // you could set an array of permissions or load them using a file 'autoload_config'
    ];
```

This is the default configuration, based on it the Authorize object will first check your ```config/permissions.php```
file and load the permissions using the configuration key ```Users.SimpleRbac.permissions```, there is an
example file you can copy into your ```config/permissions.php``` under the Plugin's config directory.

If you don't want to use a file for configuring the permissions, you just need to tweak the configuration and set
```'autoload_config' => false,``` then define all your rules in AppController (not a good practice as the rules
tend to grow over time).

The Users Plugin will use the field ```role_field``` in the Users Table to match the role of the user and
check if there is a rule allowing him to access the url.

The ```default_role``` will be used to set the role of the registered users by default.

Permission rules syntax
-----------------

* Rules are evaluated top-down, first matching rule will apply
* Each rule is defined:
```php
[
    'role' => 'REQUIRED_NAME_OF_THE_ROLE_OR_[]_OR_*',
    'prefix' => 'OPTIONAL_PREFIX_USED_OR_[]_OR_*_DEFAULT_NULL',
    'plugin' => 'OPTIONAL_NAME_OF_THE_PLUGIN_OR_[]_OR_*_DEFAULT_NULL',
    'controller' => 'REQUIRED_NAME_OF_THE_CONTROLLER_OR_[]_OR_*'
    'action' => 'REQUIRED_NAME_OF_ACTION_OR_[]_OR_*',
    'allowed' => 'OPTIONAL_BOOLEAN_OR_CALLABLE_DEFAULT_TRUE'
]
```
* If no rule allowed = true is matched for a given user role and url, default return value will be false
* Note for Superadmin access (permission to access ALL THE THINGS in your app) there is a specific Authorize Object provided

Permission Callbacks
-----------------
You could use a callback in your 'allowed' to process complex authentication, like
  - ownership
  - permissions stored in your database
  - permission based on an external service API call

Example *ownership* callback, to allow users to edit their own Posts:

```php
    'allowed' => function (array $user, $role, Request $request) {
        $postId = Hash::get($request->params, 'pass.0');
        $post = TableRegistry::get('Posts')->get($postId);
        $userId = Hash::get($user, 'id');
        if (!empty($post->user_id) && !empty($userId)) {
            return $post->user_id === $userId;
        }
        return false;
    }
```

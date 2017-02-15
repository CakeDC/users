SimpleRbacAuthorize
=============

Setup
---------------

SimpleRbacAuthorize is configured by default, but you can customize the way it works easily

Example, in your AppController, you can configure the way the SimpleRbac works by setting the options:

```php
$config['Auth']['authorize']['CakeDC/Users.SimpleRbac'] = [
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

* Permissions are evaluated top-down, first matching permission will apply
* Each permission is an associative array of rules with following structure: `'value_to_check' => 'expected_value'`
* `value_to_check` can be any key from user array or one of special keys:
    * Routing params:
        * `prefix`
        * `plugin`
        * `extension`
        * `controller`
        * `action`
    * `role` - Alias/shortcut to field defined in `role_field` config value
    * `allowed` - see below
* If you have a user field that overlaps with special keys (eg. `$user->allowed`) you can prepend `user.` to key to force matching from user array (eg. `user.allowed`)
* The keys can be placed in any order with exception of `allowed` which must be last one (see below)
* `value_to_check` can be prepended with `*` to match everything except `expected_value`
* `expected_value` can be one of following things:
    * `*` will match absolutely everything
    * A _string_/_integer_/_boolean_/etc - will match only the specified value
    * An _array_ of strings/integers/booleans/etc (can be mixed). The rule will match if real value is equal to any of expected ones
    * A callable/object (see below)
* If any of rules fail, the permission is discarded and the next one is evaluated
* A very special key `allowed` exists which has completely different behaviour:
    * If `expected_value` is a callable/object then it's executed and the result is casted to boolean
    * If `expected_value` is **not** a callable/object then it's simply casted to boolean
    * The `*` is checked and if found the result is inverted
    * The final boolean value is **the result of permission** checker. This means if it is `false` then no other permissions are checked and the user is denied access.
    For this reason the `allowed` key must be placed at the end of permission since no other rules are executed after it

**Notes**:

* For Superadmin access (permission to access ALL THE THINGS in your app) there is a specific Authorize Object provided
* Permissions that do not have `controller` and/or `action` keys (or the inverted versions) are automatically discarded in order to prevent errors.
If you need to match all controllers/actions you can explicitly do `'contoller' => '*'`
* Key `user` (or the inverted version) is illegal (as it's impossible to match an array) and any permission containing it will be discarded
* If the permission is discarded for the reasons stated above, a debug message will be logged

**Permission Callbacks**: you could use a callback in your 'allowed' to process complex authentication, like
  - ownership
  - permissions stored in your database
  - permission based on an external service API call

Example *ownership* callback, to allow users to edit their own Posts:

```php
    'allowed' => function (array $user, $role, \Cake\Network\Request $request) {
        $postId = \Cake\Utility\Hash::get($request->params, 'pass.0');
        $post = \Cake\ORM\TableRegistry::get('Posts')->get($postId);
        $userId = $user['id'];
        if (!empty($post->user_id) && !empty($userId)) {
            return $post->user_id === $userId;
        }
        return false;
    }
```

**Permission Rules**: If you see that you are duplicating logic in your callables, you can create rule class to re-use the logic.
For example, the above ownership callback is included in CakeDC\Users as `Owner` rule
```php
'allowed' => new \CakeDC\Users\Auth\Rules\Owner() //will pick by default the post id from the first pass param
```
Check the [Owner Rule](OwnerRule.md) documentation for more details

Creating rule classes
---------------------

The only requirement is to implement `\CakeDC\Users\Auth\Rules\Rule` interface which has one method:

```php
class YourRule implements \CakeDC\Users\Auth\Rules\Rule
{
    /**
     * Check the current entity is owned by the logged in user
     *
     * @param array $user Auth array with the logged in data
     * @param string $role role of the user
     * @param Request $request current request, used to get a default table if not provided
     * @return bool
     */
    public function allowed(array $user, $role, Request $request)
    {
        // Your logic here
    }
}
```

This logic can be anything: database, external auth, etc.

Also, if you are using DB, you can choose to extend `\CakeDC\Users\Auth\Rules\AbstractRule` since it provides convenience methods for reading from DB
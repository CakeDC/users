Owner Rule
=============

Setup
---------------

SimpleRbacAuthorize will pick and use specific Rule classes (implementing the \CakeDC\Users\Auth\Rules\Rule interface).
You only need to create your own permission rules, implement the required interface and pass the instance in the
'allowed' param.

We provide an AbstractRule you could extend too, in case you want to use rules accessing the database and using the
default table associated to the current controller.

Owner rule configuration
-----------------

The Owner rule can be configured to use the following options
```php
        //field in the owned table matching the user_id
        'ownerForeignKey' => 'user_id',
        /*
         * request key type to retrieve the table id, could be "params", "query", "data" to locate the table id
         * example:
         *   yoursite.com/controller/action/XXX would be
         *     tableKeyType => 'params', 'tableIdParamsKey' => 'pass.0'
         *   yoursite.com/controlerr/action?post_id=XXX would be
         *     tableKeyType => 'query', 'tableIdParamsKey' => 'post_id'
         *   yoursite.com/controller/action [posted form with a field named post_id] would be
         *     tableKeyType => 'data', 'tableIdParamsKey' => 'post_id'
         */
        'tableKeyType' => 'params',
        // request->params key path to retrieve the owned table id
        'tableIdParamsKey' => 'pass.0',
        /* define table to use or pick it from controller name defaults if null
         * if null, table used will be based on current controller's default table
         * if string, TableRegistry::get will be used
         * if Table, the table object will be used
         */
        'table' => null,
        'conditions' => [],
```

Example:

(in your permissions.php file)
```php
[
    'role' => ['user'],
    'controller' => ['Posts'],
    'action' => ['edit'],
    'allowed' => new Owner([
        'ownerForeignKey' => 'owner_id',
    ]),
],
```

In this example, action `/posts/edit/55` will be allowed if:
  * The user is logged in
  * The user role is 'user'
  * There is a post in posts table with id 55
  * The 'owner_id' field in the post matches the user id

Checking ownership in belongsToMany associations
-----------------

Let's say you have users, posts, posts_users tables, and Posts belongsToMany Users,
you could check the ownership configuring the Owner rule:
```php
[
    'role' => ['user'],
    'controller' => ['Posts'],
    'action' => ['edit'],
    'allowed' => new Owner([
        'table' => 'PostsUsers',
        'id' => 'post_id',
        'ownerForeignKey' => 'owner_id'
    ]),
],
```

In this example, action `/posts/edit/55` will be allowed if:
  * The user is logged in
  * The user role is 'user'
  * There is a row in posts_users table matching
    * 'owner_id' = user id
    * 'post_id' = 55

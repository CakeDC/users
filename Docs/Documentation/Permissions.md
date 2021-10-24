Permissions
===========
The plugin is setup to perform permissions check for all requests using
Superuser and Rbac policies.

Superuser policy allow the superuser to access any page.

The Rbac policy allows you to define a list of rules at config/permissions.php
to perform checks based on request information (prefix, plugin, controller, action, etc)
and user data.

You can find the permission rule syntax at [CakeDC/auth documentation.](https://github.com/CakeDC/auth/blob/master/Docs/Documentation/Rbac.md#permission-rules-syntax)

I want to allow access to public actions (non-logged user)
----------------------------------------------------------
To allow access to public actions (that does not requires a looged) we need to include a new rule at config/permissions.php
using the 'bypassAuth' key.

```php
<?php
return [
    'CakeDC/Auth.permissions' => [
        //...... all other rules
        [
            'controller' => 'Pages',
            'action' => ['home', 'contact', 'projects']
            'bypassAuth' => true,
        ],
    ],
];
```

I want to allow access to one specific action
---------------------------------------------
To allow access to specific action we need to include a new rule at config/permissions.php

- Path: /{controler}/{action}
```php
<?php
return [
    'CakeDC/Auth.permissions' => [
        //...... all other rules
        [
            //Allow user, manager and author roles to access /books
            'role' => ['user', 'manager', 'author'],
            'controller' => 'Books',
            'action' => 'index',
        ],
        [
            //Allow user to access /dashboard/home
            'role' => 'user',
            'controller' => 'Dashbord',
            'action' => 'home',
        ],
        [
            //Allow user to access /articles, /articles/add and /article/edit
            'role' => ['manager'],
            'controller' => 'Articles',
            'action' => ['index', 'add', 'edit'],
        ],
    ],
];
```

- Path: /{plugin}/{prefix}/{controler}/{action}
```php
<?php
return [
    'CakeDC/Auth.permissions' => [
        //...... all other rules
        [
            //Allow user to access /reports/admin/categories
            'plugin' => 'Reports',
            'prefix' => 'Admin',
            'role' => ['manager'],
            'controller' => 'Categories',
            'action' => ['index'],
        ],
    ],
];
```

I want to allow access to all actions from one controller
---------------------------------------------------------
To allow access to specific all actions from one controller we need to include a new rule at config/permissions.php
using the value '*' for 'action'  key.

```php
<?php
return [
    'CakeDC/Auth.permissions' => [
        //...... all other rules
        [
            //Allow user, manager and author roles to access any action from books controller
            'role' => ['user', 'manager', 'author'],
            'controller' => 'Books',
            'action' => '*',
        ],
    ]
];
```

I want to allow access to all controllers from one prefix
---------------------------------------------
To allow access to specific to all pages from one prefix we need to include a new rule at config/permissions.php
using the value '*' for 'plugin', 'controller' and 'action' keys.

```php
<?php
return [
    'CakeDC/Auth.permissions' => [
        //...... all other rules
        [
            //Allow user, manager and author roles to access any action from books controller
            'role' => ['user', 'manager', 'author'],
            'plugin' => '*',
            'prefix' => 'Admin',
            'controller' => '*',
            'action' => '*',
        ],
    ],
];
```

I want to allow access to entity owned by the user
--------------------------------------------------
To allow access to entity owned by the user we need to include a new rule
at config/permissions.php using the 'allowed' key.

```php
<?php
return [
    'CakeDC/Auth.permissions' => [
        //...... all other rules
        [
            //
            'role' => 'user',
            'controller' => 'Articles',
            'action' => ['edit']
            'allowed' => new \CakeDC\Auth\Rbac\Rules\Owner([
                'table' => 'Articles',
                'id' => 'id',
                'ownerForeignKey' => 'owner_id'
            ]),
        ],
    ],
];
```

[For more information check owner rule documentation](https://github.com/CakeDC/auth/blob/6.next-cake4/Docs/Documentation/OwnerRule.md)


I want to allow access to action using a custom logic
----------------------------------------------------
Permission rule can have a custom callback. Adde the rule at config/permissions.php using the 'allowed' key.

```php
<?php
return [
    'CakeDC/Auth.permissions' => [
        //...... all other rules
        [
            //
            'role' => 'user',
            'controller' => 'Posts',
            'action' => ['edit']
            'allowed' => function (array $user, $role, \Cake\Http\ServerRequest $request) {
                $postId = \Cake\Utility\Hash::get($request->params, 'pass.0');
                $post = \Cake\ORM\TableRegistry::get('Posts')->get($postId);
                $userId = $user['id'];
                if (!empty($post->user_id) && !empty($userId)) {
                    return $post->user_id === $userId;
                }
                return false;
            }
        ],
    ],
];
```

[For more information check CakeDC/Auth documentation](https://github.com/CakeDC/auth/blob/6.next-cake4/Docs/Documentation/Rbac.md#permission-callbacks)



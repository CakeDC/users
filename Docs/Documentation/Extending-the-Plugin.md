Extending the Plugin
====================

Extending the Model (Table/Entity)
-------------------

Create a new Table and Entity in your app, matching the table you want to use for storing the
users data. Check the initial users migration to know the default columns expected in the table.
If your column names doesn't match the columns in your current table, you could use the Entity to
match the colums using accessors & mutators as described here http://book.cakephp.org/3.0/en/orm/entities.html#accessors-mutators

Example: we are going to use a custom table in our application ```my_users```
* Create a new Table under src/Model/Table/MyUsersTable.php

```php
namespace App\Model\Table;

use CakeDC\Users\Model\Table\UsersTable;

/**
 * Users Model
 */
class MyUsersTable extends UsersTable
{
}
```

* Create a new Entity under src/Model/Entity/MyUser.php

```php
namespace App\Model\Entity;

use CakeDC\Users\Model\Entity\User;

class MyUser extends User
{
}
```

* Pass the new table configuration to Users Plugin Configuration

config/bootstrap.php
```
Configure::write('Users.config', ['users']);
Plugin::load('CakeDC/Users', ['routes' => true, 'bootstrap' => true]);
```

Then in your config/users.php
```
return [
    'Users.table' => 'MyUsers',
];
```

Now the Users Plugin will use MyUsers Table and Entity to register and login user in. Use the
Entity to match your own columns in case they don't match the default column names:

```sql
CREATE TABLE IF NOT EXISTS `users` (
  `id` char(36) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `token_expires` datetime DEFAULT NULL,
  `api_token` varchar(255) DEFAULT NULL,
  `activation_date` datetime DEFAULT NULL,
  `tos_date` datetime DEFAULT NULL,
  `active` int(1) NOT NULL DEFAULT '0',
  `is_superuser` int(1) NOT NULL DEFAULT '0',
  `role` varchar(255) DEFAULT 'user',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

Extending the Controller
-------------------

You want to use one of your controllers to handle all the users features in your app, and keep the
login/register/etc actions from Users Plugin,

First create a new controller class:

```php
<?php
namespace App\Controller;

use App\Controller\AppController;
use App\Model\Table\MyUsersTable;
use Cake\Event\Event;
use CakeDC\Users\Controller\Component\UsersAuthComponent;
use CakeDC\Users\Controller\Traits\LoginTrait;
use CakeDC\Users\Controller\Traits\RegisterTrait;

class MyUsersController extends AppController
{
    use LoginTrait;
    use RegisterTrait;

//add your new actions, override, etc here
}
```

Don't forget to update the `Users.controller` configuration in `users.php`

```php
    'Users' => [
        // ...
        // Controller used to manage users plugin features & actions
        'controller' => 'MyUsers',
        // ...
```

Note you'll need to **copy the Plugin templates** you need into your project src/Template/MyUsers/[action].ctp

Extending the Features in your controller
-----------------------------

You could use a new Trait. For example, you want to add an 'impersonate' feature

```php
<?php
namespace App\Controller\Traits;

use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Network\Exception\NotFoundException;

/**
 * Impersonate Trait
 */
trait ImpersonateTrait
{
    /**
     * Adding a new feature as an example: Impersonate another user
     *
     * @param type $userId
     */
    public function impersonate($userId)
    {
        $user = $this->getUsersTable()->find()
                ->where(['id' => $userId])
                ->hydrate(false)
                ->first();
        $this->Auth->setUser($user);
        return $this->redirect('/');
    }
}
```
Updating the Templates
-------------------

Use the standard CakePHP conventions to override Plugin views using your application views
http://book.cakephp.org/3.0/en/plugins.html#overriding-plugin-templates-from-inside-your-application




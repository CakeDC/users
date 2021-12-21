Extending the Plugin
====================

Extending the Model (Table/Entity)
-------------------

Create a new Table and Entity in your app, matching the table you want to use for storing the
users data. Check the initial users migration to know the default columns expected in the table.
If your column names doesn't match the columns in your current table, you could use the Entity to
match the colums using accessors & mutators as described here https://book.cakephp.org/4/en/orm/entities.html#accessors-mutators

Example: we are going to use a custom table ```my_users``` in our application , which has a field named ``is_active`` instead of the default ``active``.
* Create a new Table under src/Model/Table/MyUsersTable.php

```php
namespace App\Model\Table;

use CakeDC\Users\Model\Table\UsersTable;

/**
 * Application specific Users Table with non plugin conform field(s)
 */
class MyUsersTable extends UsersTable
{
}
```

* Create a new Entity under src/Model/Entity/MyUser.php

```php
namespace App\Model\Entity;

use CakeDC\Users\Model\Entity\User;

/**
 * Application specific User Entity with non plugin conform field(s)
 */
class MyUser extends User
{
    /**
     * Map CakeDC's User.active field to User.is_active when getting
     *
     * @return mixed The value of the mapped property.
     */
    protected function _getActive()
    {
        return $this->_properties['is_active'];
    }

    /**
     * Map CakeDC's User.active field to User.is_active when setting
     *
     * @param mixed $value The value to set.
     * @return static
     */
    protected function _setActive($value)
    {
        $this->set('is_active', $value);
        return $value;
    }
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
Entity as shown above to match your own columns in case they don't match the default column names:

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
use CakeDC\Users\Controller\Traits\LoginTrait;
use CakeDC\Users\Controller\Traits\RegisterTrait;

class MyUsersController extends AppController
{
    use LoginTrait;
    use RegisterTrait;

    /**
     * Initialize
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('CakeDC/Users.Setup');
        if ($this->components()->has('Security')) {
            $this->Security->setConfig(
                'unlockedActions',
                ['login', 'u2fRegister', 'u2fRegisterFinish', 'u2fAuthenticate', 'u2fAuthenticateFinish']
            );
        }
    }

    //add your new actions, override, etc here
}
```

Don't forget to update the `Users.controller` configuration in `users.php` this is
needed to setup correct url/route for authentication.

```php
    'Users' => [
        // ...
        // Controller used to manage users plugin features & actions
        'controller' => 'MyUsers',
        // ...
```

**You also need to update permissions rules in your file config/permissions.php
to match the new controller.**

Note you'll need to **copy the Plugin templates** you need into your project templates/MyUsers/[action].php

You may also need to load some helpers in your AppView:

```php
   /**
     * Initialization hook method.
     *
     * @return void
     */
    public function initialize()
    {
        $this->loadHelper('CakeDC/Users.AuthLink');
        $this->loadHelper('CakeDC/Users.User');
    }
```

Extending the Features in your controller
-----------------------------

You could use a new Trait. For example, you want to add an 'impersonate' feature

```php
<?php
namespace App\Controller\Traits;

use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Exception\NotFoundException;

/**
 * Impersonate Trait
 */
trait ImpersonateTrait
{
    /**
     * Adding a new feature as an example: Review user
     *
     * @param string $userId
     */
    public function review($userId)
    {
        //Your review logic
    }
}
```
Updating the Templates
-------------------

Use the standard CakePHP conventions to override Plugin views using your application views
https://book.cakephp.org/4/en/plugins.html#overriding-plugin-templates-from-inside-your-application

`{project_dir}/templates/plugin/CakeDC/Users/Users/{templates_in_here}`

Updating the Emails
-------------------

Extend the `\CakeDC\Users\Mailer\UsersMailer` class and override the email configuration to change the way the
emails are sent by the Plugin. We currently have:
* validation, sent with a link to validate new users registered
* resetPassword, sent with a link to access the reset password feature
* socialAccountValidation, sent with a link to validate the social account used for login

Example, to override the validation email you would need to:
* Create a new class in your application
```php
namespace App\Mailer;

use Cake\Datasource\EntityInterface;
use CakeDC\Users\Mailer\UsersMailer;

class MyUsersMailer extends UsersMailer
{
    public function resetPassword(EntityInterface $user)
    {
        parent::resetPassword($user);
        $this->setSubject('This is the new subject');
        $this->setTemplate('custom-template-in-app-namespace');
    }
}
```
* Configure the plugin to use this new mailer class, add this in your config/users.php file:

```php
  'Users.Email.mailerClass' => \App\Mailer\MyUsersMailer::class,
```

* Create the file `templates/email/text/custom_template_in_app_namespace.php`
with your custom contents. Note you can also prepare an html version of the file,
change the template, or do any other customization in the `MyUsersMailer` method.



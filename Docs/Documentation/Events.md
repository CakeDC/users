Events
======

The events in this plugin follow these conventions `<Plugin><Category>.<EventName>`:

* `Users.Authentication.afterLogin`
* `Users.Authentication.beforeLogout`
* `Users.Authentication.afterLogout`
* `Users.Global.beforeRegister`
* `Users.Global.afterRegister`
* `Users.Global.beforeSocialLoginUserCreate`
* `Users.Global.afterResetPassword`
* `Users.Global.onExpiredToken`
* `Users.Global.afterResendTokenValidation`

The events allow you to inject data into the plugin on the before* plugins and use the data for your
own business.


I want to add custom logic before user logout
---------------------------------------------
When adding a custom logic to execute before user logout you
have access to user data and the controller object. The main logout
logic will be performed if you don't assign an array to result, but if
you set it we will use as redirect url.

- Create or update file src/Event/UsersListener.php:
```php
<?php

namespace App\Event;

use Cake\Cache\Cache;
use Cake\Event\EventListenerInterface;

class UsersListener implements EventListenerInterface
{
    /**
     * @return string[]
     */
    public function implementedEvents(): array
    {
        return [
            \CakeDC\Users\Plugin::EVENT_BEFORE_LOGOUT => 'beforeLogout',
        ];
    }

    /**
     * @param \Cake\Event\Event $event
     */
    public function beforeLogout(\Cake\Event\Event $event)
    {
        $user = $event->getData('user');
        $controller = $event->getSubject();

        //your custom logic
        Cache::delete('dashboard_data_user_' . $user['id']);
        $controller->Flash->succes(__('Some message if you want'));

        //If you want to ignore the logout logic you can, just set an url array as result to use as redirect
        //$event->setResult(['plugin' => false, 'controller' => 'Page', 'action' => 'seeYouSoon']);
    }
}
```
- Add this at the end of your method Application::bootstrap if you have NOT done before.
```php
$this->getEventManager()->on(new \App\Event\UsersListener());
```

I want to add custom logic before user register
---------------------------------------------
When adding a custom logic to execute before user register you
have access to 'usersTable', 'userEntity' and 'options' keys in the event
object and the controller object.
You can also populate the new user entity or stop the register process.

- Create or update file src/Event/UsersListener.php:
```php
<?php

namespace App\Event;

use Cake\Event\EventListenerInterface;

class UsersListener implements EventListenerInterface
{
    /**
     * @return string[]
     */
    public function implementedEvents(): array
    {
        return [
            \CakeDC\Users\Plugin::EVENT_BEFORE_REGISTER => 'beforeRegister',
        ];
    }

    /**
     * @param \Cake\Event\Event $event
     */
    public function beforeRegister(\Cake\Event\Event $event)
    {
        $controller = $event->getSubject();
        $user = $event->getData('userEntity');
        $table = $event->getData('usersTable');
        $options = $event->getData('options');

        //Or custom logic
        $controller->Flash->succes(__('Some message if you want'));

        //When you set an entity as result a part of register logic is skipped (ex: reCaptcha)
        $newUser = $table->newEntity([
            'username' => 'forceEventRegister',
            'email' => 'eventregister@example.com',
            'password' => 'password',
            'active' => true,
            'tos' => true,
        ]);
        //
        $event->setResult($newUser);
        //If you want to stop registration use
        //$event->stopPropagation();
        //$event->setResult(['plugin' => false, 'controller' => 'Somewhere', 'action' => 'toRedirect']);
    }
}

```
- Add this at the end of your method Application::bootstrap if you have NOT done before.
```php
$this->getEventManager()->on(new \App\Event\UsersListener());
```

I want to add custom logic before linking social account
--------------------------------------------------------
When adding a custom logic to execute before linking social account you
have access to 'location' and 'request' keys in the event object and
the controller object.

- Create or update file src/Event/UsersListener.php:
```php
<?php

namespace App\Event;

use Cake\Event\EventListenerInterface;

class UsersListener implements EventListenerInterface
{
    /**
     * @return string[]
     */
    public function implementedEvents(): array
    {
        return [
            \CakeDC\Users\Plugin::EVENT_BEFORE_SOCIAL_LOGIN_REDIRECT => 'beforeSocialLoginRedirect',
        ];
    }

    /**
     * @param \Cake\Event\Event $event
     */
    public function beforeSocialLoginRedirect(\Cake\Event\Event $event)
    {
        $controller = $event->getSubject();
        $location = $event->getData('location');
        $request = $event->getData('request');

        //your custom logic
    }
}

```
- Add this at the end of your method Application::bootstrap if you have NOT done before.
```php
$this->getEventManager()->on(new \App\Event\UsersListener());
```

I want to add custom logic before creating social account
---------------------------------------------------------
When adding a custom logic to execute before creating social account you
have access to 'userEntity' and 'data' keys in the event object and
the social behavior object.

You can also set a new user entity object as result.

- Create or update file src/Event/UsersListener.php:
```php
<?php

namespace App\Event;

use Cake\Event\EventListenerInterface;

class UsersListener implements EventListenerInterface
{
    /**
     * @return string[]
     */
    public function implementedEvents(): array
    {
        return [
            \CakeDC\Users\Plugin::EVENT_BEFORE_SOCIAL_LOGIN_REDIRECT => 'beforeSocialLoginRedirect',
        ];
    }

    /**
     * @param \Cake\Event\Event $event
     */
    public function beforeSocialLoginRedirect(\Cake\Event\Event $event)
    {
        $userEntity = $event->getData('userEntity');
        $socialData = $event->getData('data');

        //your custom logic

        //If you want to use another entity use this
        //$event->setResult($anotherUserEntity);
    }
}
```
- Add this at the end of your method Application::bootstrap if you have NOT done before.
```php
$this->getEventManager()->on(new \App\Event\UsersListener());
```

I want to add custom logic after user login
-------------------------------------------
When adding a custom logic to execute after user login you
have access to user data. You can also set an array as result to
perform a custom redirect.

- Create or update file src/Event/UsersListener.php:
```php
<?php

namespace App\Event;

use Cake\Datasource\ModelAwareTrait;
use Cake\Event\EventListenerInterface;

class UsersListener implements EventListenerInterface
{
    use ModelAwareTrait;

    /**
     * @return string[]
     */
    public function implementedEvents(): array
    {
        return [
            \CakeDC\Users\Plugin::EVENT_AFTER_LOGIN => 'afterLogin',
        ];
    }

    /**
     * @param \Cake\Event\Event $event
     */
    public function afterLogin(\Cake\Event\Event $event)
    {
        $user = $event->getData('user');
        //your custom logic
        //$this->loadModel('SomeOptionalUserLogs')->newLogin($user);

        //If you want to use a custom redirect
        $event->setResult([
            'plugin' => false,
            'controller' => 'Dashboard',
            'action' => 'home',
        ]);
    }
}
```
- Add this at the end of your method Application::bootstrap if you have NOT done before.
```php
$this->getEventManager()->on(new \App\Event\UsersListener());
```

I want to add custom logic after user logout
---------------------------------------------
When adding a custom logic to execute after user logout you
have access to user data and the controller object. You can also
set an array as result to perform a custom redirect.

- Create or update file src/Event/UsersListener.php:
```php
<?php

namespace App\Event;

use Cake\Event\EventListenerInterface;

class UsersListener implements EventListenerInterface
{
    /**
     * @return string[]
     */
    public function implementedEvents(): array
    {
        return [
            \CakeDC\Users\Plugin::EVENT_AFTER_LOGOUT => 'afterLogout',
        ];
    }

    /**
     * @param \Cake\Event\Event $event
     */
    public function afterLogout(\Cake\Event\Event $event)
    {
        $user = $event->getData('user');
        $controller = $event->getSubject();
        //your custom logic

        //If you want to use a custom redirect
        $event->setResult([
            'plugin' => false,
            'controller' => 'Pages',
            'action' => 'thankYou',
        ]);
    }
}

```
- Add this at the end of your method Application::bootstrap if you have NOT done before.
```php
$this->getEventManager()->on(new \App\Event\UsersListener());
```

I want to add custom logic after user register
----------------------------------------------
When adding a custom logic to execute after user register you
have access to user data and the controller object. You can also
set a custom http response as result to render a different content
or perform a custom redirect.

- Create or update file src/Event/UsersListener.php:
```php
<?php

namespace App\Event;

use Cake\Event\EventListenerInterface;

class UsersListener implements EventListenerInterface
{
    /**
     * @return string[]
     */
    public function implementedEvents(): array
    {
        return [
            \CakeDC\Users\Plugin::EVENT_AFTER_REGISTER => 'afterRegister',
        ];
    }

    /**
     * @param \Cake\Event\Event $event
     */
    public function afterRegister(\Cake\Event\Event $event)
    {
        $user = $event->getData('user');
        $controller = $event->getSubject();
        //your custom logic

        //If you want to use a custom response to render a json.
        $response = $controller->getResponse()->withStringBody(json_encode(['success' => true, 'id' => $user['id']]));
        $event->setResult($response);

        //or if you want to use a custom redirect.
        $response = $controller->getResponse()->withLocation("/some/page");
        $event->setResult($response);
    }
}

```
- Add this at the end of your method Application::bootstrap if you have NOT done before.
```php
$this->getEventManager()->on(new \App\Event\UsersListener());
```

I want to add custom logic after user changed the password
----------------------------------------------------------
When adding a custom logic to execute after user change the password
you have access to some user data and the controller object. You can also
set an array as result to perform a custom redirect.

- Create or update file src/Event/UsersListener.php:
```php
<?php

namespace App\Event;

use Cake\Event\EventListenerInterface;

class UsersListener implements EventListenerInterface
{
    /**
     * @return string[]
     */
    public function implementedEvents(): array
    {
        return [
            \CakeDC\Users\Plugin::EVENT_AFTER_CHANGE_PASSWORD => 'afterChangePassword',
        ];
    }

    /**
     * @param \Cake\Event\Event $event
     */
    public function afterChangePassword(\Cake\Event\Event $event)
    {
        $user = $event->getData('user');
        $controller = $event->getSubject();
        //your custom logic

        //If you want to use a custom redirect
        $event->setResult([
            'plugin' => false,
            'controller' => 'Pages',
            'action' => 'infoPassword',
        ]);
    }
}

```
- Add this at the end of your method Application::bootstrap if you have NOT done before.
```php
$this->getEventManager()->on(new \App\Event\UsersListener());
```


I want to add custom logic after sending the token for user validation
----------------------------------------------------------------------
When adding a custom logic to execute after sending the token for user
validation you can also set an array as result to perform a custom redirect.

- Create or update file src/Event/UsersListener.php:
```php
<?php

namespace App\Event;

use Cake\Event\EventListenerInterface;

class UsersListener implements EventListenerInterface
{
    /**
     * @return string[]
     */
    public function implementedEvents(): array
    {
        return [
            \CakeDC\Users\Plugin::EVENT_AFTER_RESEND_TOKEN_VALIDATION => 'afterResendTokenValidation',
        ];
    }

    /**
     * @param \Cake\Event\Event $event
     */
    public function afterResendTokenValidation(\Cake\Event\Event $event)
    {
        $controller = $event->getSubject();
        //your custom logic

        //If you want to use a custom redirect
        $event->setResult([
            'plugin' => false,
            'controller' => 'Pages',
            'action' => 'infoValidation',
        ]);
    }
}

```
- Add this at the end of your method Application::bootstrap if you have NOT done before.
```php
$this->getEventManager()->on(new \App\Event\UsersListener());
```

I want to add custom logic after user email is validated
--------------------------------------------------------
When adding a custom logic to execute after user email is validate
you have access to some user data and the controller object. You can also
set an array as result to perform a custom redirect.

- Create or update file src/Event/UsersListener.php:
```php
<?php

namespace App\Event;

use Cake\Event\EventListenerInterface;

class UsersListener implements EventListenerInterface
{
    /**
     * @return string[]
     */
    public function implementedEvents(): array
    {
        return [
            \CakeDC\Users\Plugin::EVENT_AFTER_EMAIL_TOKEN_VALIDATION => 'afterEmailTokenValidation',
        ];
    }

    /**
     * @param \Cake\Event\Event $event
     */
    public function afterEmailTokenValidation(\Cake\Event\Event $event)
    {
        $user = $event->getData('user');
        $controller = $event->getSubject();
        //your custom logic

        //If you want to use a custom redirect
        $event->setResult([
            'plugin' => false,
            'controller' => 'Pages',
            'action' => 'infoPassword',
        ]);
    }
}

```
- Add this at the end of your method Application::bootstrap if you have NOT done before.
```php
$this->getEventManager()->on(new \App\Event\UsersListener());
```

I want to add custom logic after user email is validated to autologin user
--------------------------------------------------------------------------
This is how you can autologin the user after email is validate:

- Create or update file src/Event/UsersListener.php:
```php
<?php

namespace App\Event;

use Cake\Event\EventListenerInterface;

class UsersListener implements EventListenerInterface
{
    /**
     * @return string[]
     */
    public function implementedEvents(): array
    {
        return [
            \CakeDC\Users\Plugin::EVENT_AFTER_EMAIL_TOKEN_VALIDATION => 'afterEmailTokenValidation',
        ];
    }

    /**
     * @param \Cake\Event\Event $event
     */
    public function afterEmailTokenValidation(\Cake\Event\Event $event)
    {
        $table = $this->loadModel('Users');
        $userData = $event->getData('user');
        $user = $table->get($userData['id']);
        $this->Authentication->setIdentity($user);
    }
}

```
- Add this at the end of your method Application::bootstrap if you have NOT done before.
```php
$this->getEventManager()->on(new \App\Event\UsersListener());
```

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
own business, for example

    /**
     * beforeRegister event
     */
    public function eventRegister()
    {
        $this->eventManager()->on(Plugin::EVENT_BEFORE_REGISTER, function ($event) {
            //the callback function should return the user data array to force register
            return $event->data['usersTable']->newEntity([
                'username' => 'forceEventRegister',
                'email' => 'eventregister@example.com',
                'password' => 'password',
                'active' => true,
                'tos' => true,
            ]);
        });
        $this->register();
        $this->render('register');
    }


How to make an autologin using `EVENT_AFTER_EMAIL_TOKEN_VALIDATION` event

```php
EventManager::instance()->on(
    \CakeDC\Users\Plugin::EVENT_AFTER_EMAIL_TOKEN_VALIDATION,
    function($event){
        $users = $this->getTableLocator()->get('Users');
        $user = $users->get($event->getData('user')->id);
        $this->Authentication->setIdentity($user);
    }
);
```

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

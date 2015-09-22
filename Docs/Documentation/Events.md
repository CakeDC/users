Events
======

The events in this plugin follow these conventions <Plugin>.<MVC>.<Name>.<EventName>:

* 'Users.Component.UsersAuth.isAuthorized'
* 'Users.Component.UsersAuth.beforeLogin'
* 'Users.Component.UsersAuth.afterLogin'
* 'Users.Component.UsersAuth.afterCookieLogin'
* 'Users.Component.UsersAuth.beforeRegister'
* 'Users.Component.UsersAuth.afterRegister'
* 'Users.Component.UsersAuth.beforeLogout'
* 'Users.Component.UsersAuth.afterLogout'

The events allow you to inject data into the plugin on the before* plugins and use the data for your
own business login in the after* events, for example

```
    /**
     * Forced login using a beforeLogin event
     */
    public function eventLogin()
    {
        $this->eventManager()->on(UsersAuthComponent::EVENT_BEFORE_LOGIN, function () {
            //the callback function should return the user data array to force login
            return [
                'id' => 1337,
                'username' => 'forceLogin',
                'email' => 'event@example.com',
                'active' => true,
            ];
        });
        $this->login();
        $this->render('login');
    }

    /**
     * beforeRegister event
     */
    public function eventRegister()
    {
        $this->eventManager()->on(UsersAuthComponent::EVENT_BEFORE_REGISTER, function ($event) {
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
```
Events
======

The events in this plugin follow these conventions `<Plugin><Category>.<EventName>`:

* `Users.Authentication.afterLogin`
* `Users.Authentication.beforeLogout`
* `Users.Authentication.afterLogout`
* `Users.Managment.beforeRegister`
* `Users.Managment.afterRegister`
* `Users.Managment.beforeSocialLoginUserCreate`
* `Users.Managment.afterResetPassword`
* `Users.Managment.onExpiredToken`
* `Users.Managment.afterResendTokenValidation`

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
```

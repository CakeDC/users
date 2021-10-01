Events
======

The events in this plugin follow these conventions `<Plugin><Category>.<EventName>`:

* `Users.Authentication.afterLogin`
* `Users.Authentication.beforeLogout`
* `Users.Authentication.afterLogout`
* `Users.Authentication.failedLogin`
* `Users.Global.beforeRegister`
* `Users.Global.afterRegister`
* `Users.Global.beforeSocialLoginUserCreate`
* `Users.Global.afterResetPassword`
* `Users.Global.onExpiredToken`
* `Users.Global.afterResendTokenValidation`

The events allow you to inject data into the plugin on the before* plugins and use the data for your
own business, for example

```php
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

How to redirect to an account lockout action, if the user fails the login 3 times, using `EVENT_FAILED_LOGIN` event
```php
EventManager::instance()->on(\CakeDC\Users\Plugin::EVENT_FAILED_LOGIN, function ($event) {
    $session = $this->request->getSession();
    $count = ($session->read('failed_login_count') ?? 0) + 1;
    $max = 3;
    
    if ($count >= $max) {
        $this->Flash->error(__('You already have {0} failed attempts', $max));
        $session->delete('failed_login_count');

        return [
            'controller' => 'Users',
            'action' => 'blockAccount',
        ];
    }

    $this->Flash->error(__('You already have {0} failed attempts, when reach {1} your account will be blocked', $count, $max));
    $session->write('failed_login_count', $count);

    return null;
});
```

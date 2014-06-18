Events
======

If you're not familiar with events look them up in [the official documentation](http://book.cakephp.org/2.0/en/core-libraries/events.html).

Events follow these conventions:

* Users.Controller.Users.someCallBack
* Users.Model.User.someCallBack
* ...

Triggered events are:

 * Users.Controller.Users.beforeRegister
 * Users.Controller.Users.afterRegister
 * Users.Controller.Users.beforeLogin
 * Users.Controller.Users.afterLogin
 * Users.Model.User.beforeRegister
 * Users.Model.User.afterRegister
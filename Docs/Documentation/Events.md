Events
======

If you're not familiar with events please look them up in [the official documentation](http://book.cakephp.org/2.0/en/core-libraries/events.html).

The events in this plugin follow these conventions <Plugin>.<MVC>.<Name>.<EventName>:

* Users.Controller.Users.someCallBack
* Users.Model.User.someCallBack
* ...

Events that are triggered in this plugin are:

 * Users.Controller.Users.beforeRegister
 * Users.Controller.Users.afterRegister
 * Users.Controller.Users.beforeLogin
 * Users.Controller.Users.afterLogin
 * Users.Model.User.beforeRegister
 * Users.Model.User.afterRegister
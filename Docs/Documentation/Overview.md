Overview
========

You can use the plugin as it comes if you're happy with it or, more common, extend your app specific user implementation from the plugin.

The plugin itself is already capable of:

* User registration (Enable by default)
* Account verification by a token sent via email
* User login (email / password)
* Password reset based on requesting a token by email and entering a new password
* User search (requires the [CakeDC Search](http://github.com/CakeDC/search) plugin)
* User management using the "admin" section (add / edit / delete)
* Simple roles management

The default password reset process requires the user to enter his email address, an email is sent to the user with a link and a token. When the user accesses the URL with the token he can enter a new password.

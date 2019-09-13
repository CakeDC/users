CakeDC Users Plugin
===================

[![Build Status](https://secure.travis-ci.org/CakeDC/users.png?branch=master)](http://travis-ci.org/CakeDC/users)
[![Coverage Status](https://img.shields.io/codecov/c/gh/CakeDC/users.svg?style=flat-square)](https://codecov.io/gh/CakeDC/users)
[![Downloads](https://poser.pugx.org/CakeDC/users/d/total.png)](https://packagist.org/packages/CakeDC/users)
[![Latest Version](https://poser.pugx.org/CakeDC/users/v/stable.png)](https://packagist.org/packages/CakeDC/users)
[![License](https://poser.pugx.org/CakeDC/users/license.svg)](https://packagist.org/packages/CakeDC/users)

Versions and branches
---------------------

| CakePHP | CakeDC Users Plugin | Tag   | Notes |
| :-------------: | :------------------------: | :--:  | :---- |
| ^3.6             | [master](https://github.com/cakedc/users/tree/master)                      | 8.0.0 | stable |
| ^3.6             | [develop](https://github.com/cakedc/users/tree/develop)                      | - | unstable |
| 3.5             | [6.x](https://github.com/cakedc/users/tree/6.x)                      | 6.0.1 | stable |
| 3.4             | [5.x](https://github.com/cakedc/users/tree/5.x)                      | 5.2.0 | stable |
| 3.3             | [4.x](https://github.com/cakedc/users/tree/4.x)                      | 4.2.0 | stable |
| 2.x             | [2.x](https://github.com/cakedc/users/tree/2.x)                     | 2.2.0 |stable |

The **Users** plugin is back!

It covers the following features:
* User registration
* Login/logout
* Social login (Facebook, Twitter, Instagram, Google, Linkedin, etc)
* Simple RBAC via https://github.com/CakeDC/auth
* Remember me (Cookie) via https://github.com/CakeDC/auth
* Manage user's profile
* Admin management

The plugin is here to provide users related features following 2 approaches:
* Quick drop-in working solution for users login/registration. Get users working in 5 minutes.
* Extensible solution for a bigger/custom application. You'll be able to extend:
  * UsersAuth Component
  * Use your own UsersTable
  * Use your own Controller

On the previous versions of the plugin, extensibility was an issue, and one of the main
objectives of the 3.0 rewrite is to guarantee all the pieces could be extended/reused as
easily.

Another decision made was limiting the plugin dependencies on other packages as much as possible.

Requirements
------------

* CakePHP 3.6.0+
* PHP 5.6+

Documentation
-------------

For documentation, as well as tutorials, see the [Docs](Docs/Home.md) directory of this repository.

Support
-------

For bugs and feature requests, please use the [issues](https://github.com/CakeDC/users/issues) section of this repository.

Commercial support is also available, [contact us](https://www.cakedc.com/contact) for more information.

Contributing
------------

This repository follows the [CakeDC Plugin Standard](https://www.cakedc.com/plugin-standard). If you'd like to contribute new features, enhancements or bug fixes to the plugin, please read our [Contribution Guidelines](https://www.cakedc.com/contribution-guidelines) for detailed instructions.

License
-------

Copyright 2017 Cake Development Corporation (CakeDC). All rights reserved.

Licensed under the [MIT](http://www.opensource.org/licenses/mit-license.php) License. Redistributions of the source code included in this repository must retain the copyright notice found in each file.

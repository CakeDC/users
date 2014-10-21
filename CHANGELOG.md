Changelog
=========

Release 2.1.1
-------------

https://github.com/CakeDC/users/tree/2.1.1

 * [73aa350](https://github.com/cakedc/users/commit/73aa350) Fixing an issue with pagination settings for the admin_index()
 * [3dd162d](https://github.com/cakedc/users/commit/3dd162d) Forgot password translation
 * [09499fb](https://github.com/cakedc/users/commit/09499fb) Fixing some strings in the AllUsersTest.php to make sure the naming is correct
 * [83f502b](https://github.com/cakedc/users/commit/83f502b) Changing the handling of the return_to parameter in the UsersController
 * [3e9a378](https://github.com/cakedc/users/commit/3e9a378) Refs https://github.com/CakeDC/users/issues/189 Fixing the Email default "from" setting, some CS fixes and some code refactoring as well
 * [d0f330e](https://github.com/cakedc/users/commit/d0f330e) Update Installation.md


Release 2.1.0
-------------

https://github.com/CakeDC/users/tree/2.1.0

 * [aa5b58d](https://github.com/CakeDC/users/commit/aa5b58d) Fixing the PrgComponent mock so that the plugin tests work until the fix for the 2nd arg of the PrgConstuctor appears in the master branch.
 * [f4f0726](https://github.com/CakeDC/users/commit/f4f0726) Fixing tests and working on the documentation
 * [4b0b210](https://github.com/CakeDC/users/commit/4b0b210) Replacing Set with Hash
 * [f9c07ec](https://github.com/CakeDC/users/commit/f9c07ec) Adding information about the legacy user details to the documentation
 * [50f0597](https://github.com/CakeDC/users/commit/50f0597) Adding semver and CONTRIBUTING.md
 * [a4e6a95](https://github.com/CakeDC/users/commit/a4e6a95) Fixing https://github.com/CakeDC/users/pull/174
 * [3a66dd2](https://github.com/CakeDC/users/commit/3a66dd2) Fixing some upper cased controller names in sidebar.ctp which causes the urls to not work
 * [29537ad](https://github.com/CakeDC/users/commit/29537ad) Changing .travis and updating composer.json
 * [effa068](https://github.com/CakeDC/users/commit/effa068) Fixing typo in error message
 * [835a2a5](https://github.com/CakeDC/users/commit/835a2a5) Fixing missing ' in the readme.md
 * [33b7029](https://github.com/CakeDC/users/commit/33b7029) Removed testTest
 * [69b901e](https://github.com/CakeDC/users/commit/69b901e) Controller Test Fixed
 * [aea36c3](https://github.com/CakeDC/users/commit/aea36c3) Fix testEditPassword
 * [c07e7c6](https://github.com/CakeDC/users/commit/c07e7c6) Updated deprecated assertEqual
 * [c62aa19](https://github.com/CakeDC/users/commit/c62aa19) Formatting fixes
 * [f53b19c](https://github.com/CakeDC/users/commit/f53b19c) User password edit / unit tests
 * [b63001b](https://github.com/CakeDC/users/commit/b63001b) Redundant Cookie::destroy()
 * [eea58c1](https://github.com/CakeDC/users/commit/eea58c1) Added missing dependency (Utils Plugin)
 * [4c08b47](https://github.com/CakeDC/users/commit/4c08b47) Changed portuguese translation path and fixed some strings
 * [3caf6db](https://github.com/CakeDC/users/commit/3caf6db) Fixed some linting issues
 * [226e3a4](https://github.com/CakeDC/users/commit/226e3a4) Code typos in readme
 * [f93ce41](https://github.com/CakeDC/users/commit/f93ce41) Minor improvement to the flash message that shows the username in the UsersController.php
 * [b6ee0ad](https://github.com/CakeDC/users/commit/b6ee0ad) Refs https://github.com/CakeDC/users/issues/145, fixing the links in the sidebar when not used from the plugin
 * [720ebb5](https://github.com/CakeDC/users/commit/720ebb5) Refs https://github.com/CakeDC/users/pull/146
 * [46d6321](https://github.com/CakeDC/users/commit/46d6321) Renamed locale fre => fra, since 2.3 CakePHP uses ISO standard.
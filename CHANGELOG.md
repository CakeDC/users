Changelog
=========

Releases for CakePHP 3
-------------

* 5.0.0
  * Some Auth objects refactored into https://github.com/CakeDC/auth
  * Upgrade to CakePHP 3.4

* 4.2.1
  * Improvements in unit tests

* 4.2.0
  * New configuration param `Users.Registration.defaultRole` to set the default role on user registration or addUser Shell action

* 4.1.3
  * Configurable rememberMe checkbox status
  * Update brazilian portuguese translations
  * Add active finder to SocialAccountsTable
  * Improvements in UsersShell for superuser add options
  * Update to robthree/twofactorauth 1.6
  * UserHelper improvements

* 4.1.2
  * Fix RememberMe redirect
  * Fix AuthLink rendering inside Cells
  
* 4.1.1
  * Add missing password field in add user

* 4.1.0
  * Add reset action for Google Authenticator

* 4.0.0
  * Add Google Authenticator
  * Add improvements to SimpleRbac, like star to invert rules and `user.` prefix to match values from the user array
  * Add `allowed` to manage the AuthLinkHelper when action is allowed
  * Add option to configure the api table and finder in ApiKeyAuthenticate

* 3.2.5
  * Fixed RegisterBehavior api, make getRegisterValidators public.

* 3.2.3
  * Added compatibility with CakePHP 3.3+ 
  * Fixed several bugs, including regression issue with Facebook login & improvements

* 3.2.2
  * Fix bug with socialLogin links not being displayed for unauthenticated users

* 3.2.1
  * New Translations (see https://github.com/CakeDC/users/blob/master/Docs/Documentation/Translations.md)
  * Stateless API Authenticate support
  * Prefix and extension support in permission rules (RBAC)
  * Improved registration and reset password user already logged in logic
  * Several bugfixes
  * AuthLinkHelper added to render links if user is allowed only
  
* 3.1.5
  * SocialAuthenticate improvements
  * Authorize Rules. Owner rule
  * Docs improvements

* 3.1.4
  * SocialAuthenticate refactored to drop Opauth in favor of Muffin/OAuth2 and league/oauth2

* 3.1.3
  * UserHelper improvements

* 3.1.2
  * Fixes in RBAC permission matchers

* 3.1.0 Migration to CakePHP 3.0
  * Unit test coverage improvements
  * Refactor UsersTable to Behavior
  * Add google authentication
  * Add reCaptcha
  * Link social accounts in profile

Releases for CakePHP 2
-------------

* 2.1.3
  * Fixed unit tests for compatibility with CakePHP 2.7

* 2.1.2
  * Minor improvements
  * New translations (german and portuguese)

* 2.1.1
  * Forgot password

* 2.1.0
  * Bugfixes and improvements

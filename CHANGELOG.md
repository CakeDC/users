Changelog
=========

Releases for CakePHP 3
-------------
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

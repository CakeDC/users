Changelog
=========
Releases for CakePHP 4
-------------
* 9.2.0
  * Switch to github actions
  * New event AfterEmailTokenValidation
  * Remove deprecations

* 9.0.5
  * Added change api token shell command

* 9.0.4
  * Fixed deprecations and stan issues
  * Improved docs
  * Fixed issue where RememberMe cookie
  * Fixed deprecated UserHelper::isAuthorized

* 9.0.3
  * Ukrainian (uk) by @yarkm13
  * Docs improvements
  * Fix DebugKit permissions issues
  
* 9.0.2
  * Added a custom Unauthorized Handler
    * If logged user access unauthorized url he is redirected to referer url or '/' if no referer url
    * If not logged user access unauthorized url he is redirected to configured url (default to login)
    * on login we only use the redirect url from querystring 'redirect' if user can access the target url
    * App can configure a callable for 'url' option to define a custom logic to retrieve the url for unauthorized redirect
  * Added postLink method to AuthLinkHelper
  * UserHelper::welcome now works with request's attribute 'identity'

* 9.0.1
  * Improved routes
  * Improved integration tests
  * Fixed warnings related to arguments in function calls

* 9.0.0
   * Migration to CakePHP 4
   * Compatible with cakephp/authentication
   * Compatible with cakephp/authorization
   * Added/removed/changed some configurations to work with new authentication/authorization plugins, [please check Migration guide for more info](https://github.com/CakeDC/users/blob/9.next/Docs/Documentation/Migration/8.x-9.0.md).
   * Events constants were moved/removed from AuthComponent to Plugin class and their values was also updated, [please check Migration guide for more info](https://github.com/CakeDC/users/blob/9.next/Docs/Documentation/Migration/8.x-9.0.md).
   * Migrated usage of AuthComponent to Authorization/Authentication plugins.

Releases for CakePHP 3
-------------
* 8.5.1
  * Added new `UsersAuthComponent::EVENT_SOCIAL_LOGIN_EXISTING_ACCOUNT`
* 8.5.0
  * Added new `UsersAuthComponent::EVENT_BEFORE_SOCIAL_LOGIN_REDIRECT`
  * Added finder to get existing social account
  * Improved social login to updated social account when account already exists
  * Improved URLs in template to avoid issue in prefixed routes

* 8.4.0
  * Rehash password if needed at login

* 8.3.0
  * Bootstrap don't need to listen for EVENT_FAILED_SOCIAL_LOGIN

* 8.2.1
  * Fix scope in facebook social login

* 8.2
  * Removed deprecations for CakePHP 3.7

* 8.1
  * Added Yubico U2F Authentication

* 8.0.3
  * Updated to latest version of Google OAuth
  * Added plugin object
  * Fixed action changePassword to work with post or put request

* 8.0.2
  * Add default role for users registered via social login

* 8.0.1
  * Fixed 2fa link preserve querystring

* 8.0.0
  * Added new events `Users.Component.UsersAuth.onExpiredToken` and `Users.Component.UsersAuth.afterResendTokenValidation`
  * Added 2 factor authentication checkers to allow customization
  * Added Mapper classes to social auth services as a way to generalize url/avatar retrieval
  * Fix issues with recent changes in Facebook API
  * Added new translations
  * Improved customization options for recaptcha integration

* 7.0.2
  * Fixed an issue with 2FA only working on the second try

* 7.0.1
  * Fixed a security issue in 2 factor authentication, reported by @ndm2
  * Updated to cakedc/auth ^3.0
  * Documentation fixes

* 7.0.0
  * Removed deprecations for CakePHP 3.6
  * Added a new `UsersAuthComponent::EVENT_AFTER_CHANGE_PASSWORD`
  * Updated docs

* 6.0.0
  * Removed deprecations and orWhere usage
  * Amazon login implemented
  * Fixed issues with login via twitter
  * Updated Facebook Graph version to 2.8
  * Fixed flash error messages on logic
  * Added link social account feature for twitter
  * Switched to codecov

* 5.2.0
  * Compatible with 3.5, deprecations will be removed in next major version of the plugin
  * Username is now custom in SocialBehavior
  * Better handling of the RememberMe checkbox
  * Updated CakeDC/Auth to use ^2.0
  * Use of UsersMailer class, and allow override of the emails sent by the plugin
  * Better token generation via randomBytes
  * Improved documentation
  * Fixed bugs reported

* 5.1.0
  * New resend validation method in RegisterBehavior
  * Allow upgrade to CakePHP 3.5.x
  * New feature connect social account
  * New polish translations
  * Fixed bugs reported

* 5.0.3
  * Implemented event dispatching on social login
  * Fixed bugs reported
  * Don't check for allowed actions in other controllers

* 5.0.2
  * Fixed bug parsing rule urls when application installed in a subdirectory

* 5.0.1
  * Bugfix release
  * Minor BR language improvements

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

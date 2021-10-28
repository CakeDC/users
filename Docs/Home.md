Home
====

The **Users** Plugin allow users to register and login, manage their profile, etc. It also allows admins to manage the users.

The plugin is thought as a base to extend your app specific users controller and model from.

That it works out of the box doesn't mean it is thought to be used exactly like it is but to provide you a kick start.

Documentation
-------------

* [Overview](Documentation/Overview.md)
* [Installation](Documentation/Installation.md)
* [Configuration](Documentation/Configuration.md)
* [Authentication](Documentation/Authentication.md)
* [Authorization](Documentation/Authorization.md)
* [SimpleRbacAuthorize](https://github.com/CakeDC/auth/blob/master/Docs/Documentation/SimpleRbacAuthorize.md)
* [SuperuserAuthorize](https://github.com/CakeDC/auth/blob/master/Docs/Documentation/SuperuserAuthorize.md)
* [Intercept Login Action](Documentation/InterceptLoginAction.md)
* [Social Authentication](Documentation/SocialAuthentication.md)
* [Google Authenticator](Documentation/Two-Factor-Authenticator.md)
* [Yubico U2F](Documentation/Yubico-U2F.md)
* [UserHelper](Documentation/UserHelper.md)
* [AuthLinkHelper](Documentation/AuthLinkHelper.md)
* [Events](Documentation/Events.md)
* [Extending the Plugin](Documentation/Extending-the-Plugin.md)
* [Translations](Documentation/Translations.md)

I want to
---------
* extend the
  * [model](Documentation/Extending-the-Plugin.md#extending-the-model-tableentity)
  * [controller](Documentation/Extending-the-Plugin.md#extending-the-controller)
  * [templates](Documentation/Extending-the-Plugin.md#updating-the-templates)

* enable or disable
  * <details>
      <summary>email validation</summary>

      Add this to your config/users.php file to disable email validation

      ```php
        'Users.Email.validate' => false,
      ```
      or this to enable (default)

      ```php
        'Users.Email.validate' => true,
      ```
    </details>
  * <details>
      <summary>registration</summary>

      Add this to your config/users.php file to disable registration

      ```php
      'Users.Registration.active' => false,
      ```
      or this to enable (default)

      ```php
      'Users.Registration.active' => true,
      ```
      </details>
  * <details>
      <summary>reCaptcha on registration</summary>

      To enable reCaptcha you need to register your site at google reCaptcha console
      and add this to your config/users.php file to enable on registration:

      ```php
      'Users.reCaptcha.key' => 'YOUR RECAPTCHA KEY',
      'Users.reCaptcha.secret' => 'YOUR RECAPTCHA SECRET',
      'Users.reCaptcha.registration' => true,
      ```
      To disable (default) add this to your config/users.php

      ```php
      'Users.reCaptcha.registration' => false,
      ```
      </details>
  * <details>
      <summary>reCaptcha on login</summary>

      To enable reCaptcha you need to register your site at google reCaptcha console
      and add this to your config/users.php file to enable on login:

      ```php
      'Users.reCaptcha.key' => 'YOUR RECAPTCHA KEY',
      'Users.reCaptcha.secret' => 'YOUR RECAPTCHA SECRET',
      'Users.reCaptcha.login' => true,
      ```
      To disable (default) add this to your config/users.php

      ```php
      'Users.reCaptcha.login' => false,
      ```
      </details>
  * [social login](./Documentation/SocialAuthentication.md#setup)
  * [OTP Two-factor authenticator](./Documentation/Two-Factor-Authenticator.md)
  * [Yubico Key U2F Two-factor authenticator](./Documentation/Yubico-U2F.md)
  * <details>
      <summary>Authentication component</summary>

      Add this to your config/users.php file to autoload the component (default):

      ```php
      'Auth.AuthenticationComponent.load' => true,
      ```

      To not autoload add this to your config/users.php

      ```php
      'Auth.AuthenticationComponent.load' => false,
      ```
    </details>
  * <details>
      <summary>Authorization component</summary>

      Add this to your config/users.php file to autoload the component (default):

      ```php
        'Auth.AuthorizationComponent.enabled' => true,
      ```

      To not autoload add this to your config/users.php

      ```php
        'Auth.AuthorizationComponent.enabled' => false,
      ```
  </details>

  * <details>
      <summary>TOS validation</summary>

      Add this to your config/users.php file to enable (default):

      ```php
        'Users.Tos.required' => true,
      ```

      To disable add this to your config/users.php

      ```php
        'Users.Tos.required' => false,
      ```
  </details>

  * <details>
      <summary>remember me</summary>

      Add this to your config/users.php file to enable (default):

      ```php
        'Users.RememberMe.active' => true,
      ```

      To disable add this to your config/users.php

      ```php
        'Users.RememberMe.active' => false,
      ```
  </details>

- allow access to
  - [public actions (non-logged user)](./Documentation/Permissions.md#i-want-to-allow-access-to-public-actions-non-logged-user)
  - [one specific action](./Documentation/Permissions.md#i-want-to-allow-access-to-one-specific-action)
  - [all actions from one controller](./Documentation/Permissions.md#i-want-to-allow-access-to-all-actions-from-one-controller)
  - [all controllers from one prefix](./Documentation/Permissions.md#i-want-to-allow-access-to-all-controllers-from-one-prefix)
  - [entity owned by the user](./Documentation/Permissions.md#i-want-to-allow-access-to-entity-owned-by-the-user)
  - [action using a custom logic](./Documentation/Permissions.md#i-want-to-allow-access-to-action-using-a-custom-logic)

- customize my login page to
  -  <details>
      <summary>use my template</summary>
      Copy the login file from `{project_dir}/vendor/cakedc/users/templates/Users/`
      to `{project_dir}/templates/plugin/CakeDC/Users/Users`.
  </details>

  -  <details>
     <summary>use a custom finder</summary>
     First add this to your config/users.php:

     ```
     'Auth.Identifiers.Password.resolver.finder' => 'myFinderName',
     'Auth.Identifiers.Social.authFinder' => 'myFinderName',
     'Auth.Identifiers.Token.resolver.finder' => 'myFinderName',
     ```
     Important: You must have extended the model, see how to at [Extending the Plugin](Documentation/Extending-the-Plugin.md)
  </details>

  - <details>
     <summary>use a custom redirect url</summary>
     To use a custom redirect url on login add this to your config/users.php:

     ```
     'Auth.AuthenticationComponent.loginRedirect' => '/some/url/',
     ```
     or
     ```
     'Auth.AuthenticationComponent.loginRedirect' => ['plugin' => false, 'controller' => 'Example', 'action' => 'home'],
     ```
    Important: when using array you should pass `'plugin' => false,` to match your app controller.
  </details>

  - <details>
    <summary>enable|disable reCaptcha</summary>

    To enable reCaptcha you need to register your site at google reCaptcha console
    and add this to your config/users.php file to enable on login:

    ```php
    'Users.reCaptcha.login' => true,
    'Users.reCaptcha.key' => 'YOUR RECAPTCHA KEY',
    'Users.reCaptcha.secret' => 'YOUR RECAPTCHA SECRET',
    ```
    To disable (default) add this to your config/users.php
    ```php
    'Users.reCaptcha.login' => false,
    ```
    </details>
  - [use user's email to login](./Documentation/Configuration.md#using-the-users-email-to-login)
  - [override the password hasher](./Documentation/Configuration.md#password-hasher-customization)

- add custom logic before
  - [user logout](./Documentation/Events.md#i-want-to-add-custom-logic-before-user-logout)
  - [user register](./Documentation/Events.md#i-want-to-add-custom-logic-before-user-register)
  - [linking social account](./Documentation/Events.md#i-want-to-add-custom-logic-before-linking-social-account)
  - [creating social account](./Documentation/Events.md#i-want-to-add-custom-logic-before-creating-social-account)

- add custom logic after
    - [user login](./Documentation/Events.md#i-want-to-add-custom-logic-after-user-login)
    - [user logout](./Documentation/Events.md#i-want-to-add-custom-logic-after-user-logout)
    - [user register](./Documentation/Events.md#i-want-to-add-custom-logic-after-user-register)
    - [user changed the password](./Documentation/Events.md#i-want-to-add-custom-logic-after-user-changed-the-password)
    - [sending the token for user validation](./Documentation/Events.md#i-want-to-add-custom-logic-after-sending-the-token-for-user-validation)
    - [user email is validated](./Documentation/Events.md#i-want-to-add-custom-logic-after-user-email-is-validated)
    - [user email is validated to autologin user](./Documentation/Events.md#i-want-to-add-custom-logic-after-user-email-is-validated-to-autologin-user)
    - [intercept login action](./Documentation/InterceptLoginAction.md)


Migration guides
----------------

* [4.x to 5.0](Documentation/Migration/4.x-5.0.md)
* [6.x to 7.0](Documentation/Migration/6.x-7.0.md)
* [8.x to 9.0](Documentation/Migration/8.x-9.0.md)

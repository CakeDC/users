Social Authentication
=====================

We currently support the following providers to perform login as well as to link an existing account:

* Facebook
* Twitter
* Google
* LinkedIn
* Instagram
* Amazon

Please [contact us](https://cakedc.com/contact) if you need to support another provider.

The main source code for social integration is provided by ['CakeDC/auth' plugin](https://github.com/cakedc/auth)

Setup
-----
By default social login is disabled, to enable you need to create the
Facebook/Twitter applications you want to use and update your file config/users.php with:

```php
//This enable social login (authentication)
'Users.Social.login' => true,
//This is the required config to setup facebook.
'OAuth.providers.facebook.options.clientId', 'YOUR APP ID';
'OAuth.providers.facebook.options.clientSecret', 'YOUR APP SECRET';
//This is the required config to setup twitter
'OAuth.providers.twitter.options.clientId', 'YOUR APP ID';
'OAuth.providers.twitter.options.clientSecret', 'YOUR APP SECRET';
```
Check optional configs at [config/users.php](./../../config/users.php) inside 'OAuth' key


You can also change the default settings for social authenticate  in your config/users.php file:

```php
    'Users.Email' => [
        //determines if the user should include email
        'required' => true,
        //determines if registration workflow includes email validation
        'validate' => true,
    ],
    'Users.Social' => [
        //enable social login
        'login' => false,
    ],
    'Users.Key' => [
        'Session' => [
            //session key to store the social auth data
            'social' => 'Users.social',
        ],
        //form key to store the social auth data
        'Form' => [
            'social' => 'social'
        ],
        'Data' => [
            //data key to store email coming from social networks
            'socialEmail' => 'info.email',
        ],
    ],
```

If email is required and the social network does not return the user email then the user will be required to input the email. Additionally, validation could be enabled, in that case the user will be asked to validate the email before be able to login. There are some cases where the email address already exists onto database, if so, the user will receive an email and will be asked to validate the social account in the app. It is important to take into account that the user account itself will remain active and accessible by other ways (other social network account or username/password).

In most situations you would not need to change any Oauth setting besides applications details.

For new facebook aps you must use the graphApiVersion 2.8 or greater:

```php
'OAuth.providers.facebook.options.graphApiVersion' => 'v2.8',
```

User Helper
-----------

You can use the helper included with the plugin to create Facebook/Twitter buttons:

In templates
```php
$this->User->facebookLogin();

$this->User->twitterLogin();
```

We recommend the use of [Bootstrap Social](http://lipis.github.io/bootstrap-social/) in order to automatically apply styles to buttons. Anyway you can always add your own style to the buttons.

Social Authentication was inspired by [UseMuffin/OAuth2](https://github.com/UseMuffin/OAuth2) library.

Custom username field
---------------------

In your customized users table, add the SocialBehavior with the following configuration:

```php
$this->addBehavior('CakeDC/Users.Social', [
    'username' => 'email'
]);
```
Or if you extend the users table, the behavior is already loaded, so just configure it with:
```php
$this->behaviors()->get('Social')->config(['username' => 'email']);
```

By default it will use `username` field.


Social Middlewares
------------------
We provide two middleware to help us the integration with social providers, the SocialAuthMiddleware is
the main one, it is responsible to redirect the user to the social provider site and setup information
needed by the CakeDC/Users.Social authenticator. The second one SocialEmailMiddleware is used when social provider does
not returns user email.

Social Authenticators
---------------------
The social authentication works with cakephp/authentication, we have two authenticators they work
in combination with the two social middlewares:
 - CakeDC/Users.Social, works with SocialAuthMiddleware
 - CakeDC/Users.SocialPendingEmai, works with SocialEmailMiddleware


Social Indentifier
------------------
The social identifier "CakeDC/Users.Social", works with data provider by both social authenticator,
it is responsible of finding or creating a user registry for the social user data request.
By default, it'll fetch user data with finder 'all', but you can use a custom one. Add this to your
config/users.php:

```php
'Auth.Identifiers.Social.authFinder' => 'customSocialAuth',
```


Handling Social Login Result
----------------------------
We use a base component 'CakeDC/Users.Login' to handle login, it checks the result of authentication
service to redirects user to an internal page or show an authentication error. It provide some error messages for social login.
There are two custom messages (Auth.SocialLoginFailure.messages) and one default message (Auth.SocialLoginFailure.defaultMessage).


To use a custom component to handle the login add this to your config/users.php file:
```php
'Auth.SocialLoginFailure.component' => 'MyLoginA',
```

The default configuration is:
```php
[
    ...
    'Auth' => [
        ...
        'SocialLoginFailure' => [
            'component' => 'CakeDC/Users.Login',
            'defaultMessage' => __d('cake_d_c/users', 'Could not proceed with social account. Please try again'),
            'messages' => [
                'FAILURE_USER_NOT_ACTIVE' => __d(
                    'cake_d_c/users',
                    'Your user has not been validated yet. Please check your inbox for instructions'
                ),
                'FAILURE_ACCOUNT_NOT_ACTIVE' => __d(
                    'cake_d_c/users',
                    'Your social account has not been validated yet. Please check your inbox for instructions'
                )
            ],
            'targetAuthenticator' => 'CakeDC\Users\Authenticator\SocialAuthenticator'
        ],
        ...
    ]
]
```

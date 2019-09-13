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
---------------------

Create the Facebook/Twitter applications you want to use and setup the configuration like this:

Config/bootstrap.php
```
Configure::write('OAuth.providers.facebook.options.clientId', 'YOUR APP ID');
Configure::write('OAuth.providers.facebook.options.clientSecret', 'YOUR APP SECRET');

Configure::write('OAuth.providers.twitter.options.clientId', 'YOUR APP ID');
Configure::write('OAuth.providers.twitter.options.clientSecret', 'YOUR APP SECRET');
```

You can also change the default settings for social authenticate:

```
Configure::write('Users', [
    'Email' => [
        //determines if the user should include email
        'required' => true,
        //determines if registration workflow includes email validation
        'validate' => true,
    ],
    'Social' => [
        //enable social login
        'login' => false,
    ],
    'Key' => [
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
]);
```

If email is required and the social network does not return the user email then the user will be required to input the email. Additionally, validation could be enabled, in that case the user will be asked to validate the email before be able to login. There are some cases where the email address already exists onto database, if so, the user will receive an email and will be asked to validate the social account in the app. It is important to take into account that the user account itself will remain active and accessible by other ways (other social network account or username/password).

In most situations you would not need to change any Oauth setting besides applications details.

For new facebook aps you must use the graphApiVersion 2.8 or greater:

```
Configure::write('OAuth.providers.facebook.options.graphApiVersion', 'v2.8');
```

User Helper
---------------------

You can use the helper included with the plugin to create Facebook/Twitter buttons:

In templates
```
$this->User->facebookLogin();

$this->User->twitterLogin();
```

We recommend the use of [Bootstrap Social](http://lipis.github.io/bootstrap-social/) in order to automatically apply styles to buttons. Anyway you can always add your own style to the buttons.

Social Authentication was inspired by [UseMuffin/OAuth2](https://github.com/UseMuffin/OAuth2) library.

Custom username field
---------------------

In your customized users table, add the SocialBehavior with the following configuration:

```php
$this->addBehavior('CakeDC.Users/Social', [
    'username' => 'email' 
]);
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
it is responsible to find or create a user registry for the request social user data.
By default it fetch user data with finder 'all', but you can use a one if you need. Add this to your
Application class, after CakeDC/Users Plugin is loaded.
```
    $identifiers = Configure::read('Auth.Identifiers');
    $identifiers['CakeDC/Users.Social']['authFinder'] = 'customSocialAuth';
    Configure::write('Auth.Identifiers', $identifiers);
```


Handling Social Login Result
----------------------------
We use a base component 'CakeDC/Users.Login' to handle tlogin, it check the result of authentication
service to redirect user to a internal page or show an authentication error. It provide some error messages for social login.
There are two custom message (Auth.SocialLoginFailure.messages) and one default message (Auth.SocialLoginFailure.defaultMessage).


To use a custom component to handle the login you could do:
```
Configure::write('Auth.SocialLoginFailure.component', 'MyLoginA');
``` 

The default configurations are:
```
[
    ...
    'Auth' => [
        ...
        'SocialLoginFailure' => [
            'component' => 'CakeDC/Users.Login',
            'defaultMessage' => __d('CakeDC/Users', 'Could not proceed with social account. Please try again'),
            'messages' => [
                'FAILURE_USER_NOT_ACTIVE' => __d(
                    'CakeDC/Users',
                    'Your user has not been validated yet. Please check your inbox for instructions'
                ),
                'FAILURE_ACCOUNT_NOT_ACTIVE' => __d(
                    'CakeDC/Users',
                    'Your social account has not been validated yet. Please check your inbox for instructions'
                )
            ],
            'targetAuthenticator' => 'CakeDC\Users\Authenticator\SocialAuthenticator'
        ],
        ...
    ]
]
``` 
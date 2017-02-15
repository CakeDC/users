SocialAuthenticate
=============

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


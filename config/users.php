<?php

/**
 * Copyright 2010 - 2017, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2017, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
use Cake\Core\Configure;
use Cake\Routing\Router;

$config = [
    'Users' => [
        // Table used to manage users
        'table' => 'CakeDC/Users.Users',
        // Controller used to manage users plugin features & actions
        'controller' => 'CakeDC/Users.Users',
        // configure Auth component
        'auth' => true,
        // Password Hasher
        'passwordHasher' => '\Cake\Auth\DefaultPasswordHasher',
        // token expiration, 1 hour
        'Token' => ['expiration' => 3600],
        'Email' => [
            // determines if the user should include email
            'required' => true,
            // determines if registration workflow includes email validation
            'validate' => true,
        ],
        'Registration' => [
            // determines if the register is enabled
            'active' => true,
            // determines if the reCaptcha is enabled for registration
            'reCaptcha' => true,
            // allow a logged in user to access the registration form
            'allowLoggedIn' => false,
            //ensure user is active (confirmed email) to reset his password
            'ensureActive' => false,
            // default role name used in registration
            'defaultRole' => 'user',
        ],
        'reCaptcha' => [
            // reCaptcha key goes here
            'key' => null,
            // reCaptcha secret
            'secret' => null,
            // use reCaptcha in registration
            'registration' => false,
            // use reCaptcha in login, valid values are false, true
            'login' => false,
        ],
        'Tos' => [
            // determines if the user should include tos accepted
            'required' => true,
        ],
        'Social' => [
            // enable social login
            'login' => false,
            // enable social login
            'authenticator' => 'CakeDC/Users.Social',
        ],
        'GoogleAuthenticator' => [
            // enable Google Authenticator
            'login' => false,
            'issuer' => null,
            // The number of digits the resulting codes will be
            'digits' => 6,
            // The number of seconds a code will be valid
            'period' => 30,
            // The algorithm used
            'algorithm' => 'sha1',
            // QR-code provider (more on this later)
            'qrcodeprovider' => null,
            // Random Number Generator provider (more on this later)
            'rngprovider' => null
        ],
        'Profile' => [
            // Allow view other users profiles
            'viewOthers' => true,
            'route' => ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'profile'],
        ],
        'Key' => [
            'Session' => [
                // session key to store the social auth data
                'social' => 'Users.social',
                // userId key used in reset password workflow
                'resetPasswordUserId' => 'Users.resetPasswordUserId',
            ],
            // form key to store the social auth data
            'Form' => [
                'social' => 'social'
            ],
            'Data' => [
                // data key to store the users email
                'email' => 'email',
                // data key to store email coming from social networks
                'socialEmail' => 'info.email',
                // data key to check if the remember me option is enabled
                'rememberMe' => 'remember_me',
            ],
        ],
        // Avatar placeholder
        'Avatar' => ['placeholder' => 'CakeDC/Users.avatar_placeholder.png'],
        'RememberMe' => [
            // configure Remember Me component
            'active' => true,
            'checked' => true,
            'Cookie' => [
                'name' => 'remember_me',
                'Config' => [
                    'expires' => '1 month',
                    'httpOnly' => true,
                ]
            ]
        ],
    ],
    'GoogleAuthenticator' => [
        'verifyAction' => [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'verify',
            'prefix' => false,
        ],
    ],
    // default configuration used to auto-load the Auth Component, override to change the way Auth works
    'Auth' => [
        'AuthenticationComponent' => [
            'loginAction' => '/login',
            'logoutRedirect' => '/login',
            'loginRedirect' => '/',
            'requireIdentity' => false
        ],
        'Authenticators' => [
            'Authentication.Session' => [
                'skipGoogleVerify' => true,
                'sessionKey' => 'Auth',
            ],
            'CakeDC/Users.Form' => [
                'loginUrl' => '/login'
            ],
            'Authentication.Token' => [
                'skipGoogleVerify' => true,
                'header' => null,
                'queryParam' => 'api_key',
                'tokenPrefix' => null,
            ],
            'CakeDC/Users.Cookie' => [
                'skipGoogleVerify' => true,
                'rememberMeField' => 'remember_me',
                'cookie' => [
                    'expires' => '1 month',
                    'httpOnly' => true,
                ],
                'loginUrl' => '/login'
            ],
        ],
        'Identifiers' => [
            'Authentication.Password',
            'Authentication.Token' => [
                'tokenField' => 'api_token'
            ]
        ],
    ],
    'SocialAuthMiddleware' => [
        'sessionAuthKey' => 'Auth',
        'locator' => [
            'usernameField' => 'username',
            'finder' => 'all',
        ]
    ],
    'OAuth' => [
        'path' => ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'socialLogin', 'prefix' => null],
        'providers' => [
            'facebook' => [
                'service' => 'CakeDC\Users\Social\Service\OAuth2Service',
                'className' => 'League\OAuth2\Client\Provider\Facebook',
                'mapper' => 'CakeDC\Users\Auth\Social\Mapper\Facebook',
                'options' => [
                    'graphApiVersion' => 'v2.8', //bio field was deprecated on >= v2.8
                    'redirectUri' => Router::fullBaseUrl() . '/auth/facebook',
                    'linkSocialUri' => Router::fullBaseUrl() . '/link-social/facebook',
                    'callbackLinkSocialUri' => Router::fullBaseUrl() . '/callback-link-social/facebook',
                ]
            ],
            'twitter' => [
                'service' => 'CakeDC\Users\Social\Service\OAuth1Service',
                'className' => 'League\OAuth1\Client\Server\Twitter',
                'mapper' => 'CakeDC\Users\Auth\Social\Mapper\Twitter',
                'options' => [
                    'redirectUri' => Router::fullBaseUrl() . '/auth/twitter',
                    'linkSocialUri' => Router::fullBaseUrl() . '/link-social/twitter',
                    'callbackLinkSocialUri' => Router::fullBaseUrl() . '/callback-link-social/twitter',
                ]
            ],
            'linkedIn' => [
                'service' => 'CakeDC\Users\Social\Service\OAuth2Service',
                'className' => 'League\OAuth2\Client\Provider\LinkedIn',
                'mapper' => 'CakeDC\Users\Auth\Social\Mapper\LinkedIn',
                'options' => [
                    'redirectUri' => Router::fullBaseUrl() . '/auth/linkedIn',
                    'linkSocialUri' => Router::fullBaseUrl() . '/link-social/linkedIn',
                    'callbackLinkSocialUri' => Router::fullBaseUrl() . '/callback-link-social/linkedIn',
                ]
            ],
            'instagram' => [
                'service' => 'CakeDC\Users\Social\Service\OAuth2Service',
                'className' => 'League\OAuth2\Client\Provider\Instagram',
                'mapper' => 'CakeDC\Users\Auth\Social\Mapper\Instagram',
                'options' => [
                    'redirectUri' => Router::fullBaseUrl() . '/auth/instagram',
                    'linkSocialUri' => Router::fullBaseUrl() . '/link-social/instagram',
                    'callbackLinkSocialUri' => Router::fullBaseUrl() . '/callback-link-social/instagram',
                ]
            ],
            'google' => [
                'service' => 'CakeDC\Users\Social\Service\OAuth2Service',
                'className' => 'League\OAuth2\Client\Provider\Google',
                'mapper' => 'CakeDC\Users\Auth\Social\Mapper\Google',
                'options' => [
                    'userFields' => ['url', 'aboutMe'],
                    'redirectUri' => Router::fullBaseUrl() . '/auth/google',
                    'linkSocialUri' => Router::fullBaseUrl() . '/link-social/google',
                    'callbackLinkSocialUri' => Router::fullBaseUrl() . '/callback-link-social/google',
                ]
            ],
            'amazon' => [
                'service' => 'CakeDC\Users\Social\Service\OAuth2Service',
                'className' => 'Luchianenco\OAuth2\Client\Provider\Amazon',
                'mapper' => 'CakeDC\Users\Auth\Social\Mapper\Amazon',
                'options' => [
                    'redirectUri' => Router::fullBaseUrl() . '/auth/amazon',
                    'linkSocialUri' => Router::fullBaseUrl() . '/link-social/amazon',
                    'callbackLinkSocialUri' => Router::fullBaseUrl() . '/callback-link-social/amazon',
                ]
            ],
        ],
    ]
];

return $config;

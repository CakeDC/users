<?php

/**
 * Copyright 2010 - 2019, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2018, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

use Cake\Routing\Router;

$config = [
    'Users' => [
        // Table used to manage users
        'table' => 'CakeDC/Users.Users',
        // Controller used to manage users plugin features & actions
        'controller' => 'CakeDC/Users.Users',
        // Password Hasher
        'passwordHasher' => '\Cake\Auth\DefaultPasswordHasher',
        'middlewareQueueLoader' => \CakeDC\Users\Loader\MiddlewareQueueLoader::class,
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
        ],
        'Profile' => [
            // Allow view other users profiles
            'viewOthers' => true,
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
                    'expires' => new \DateTime('+1 month'),
                    'httponly' => true,
                ]
            ]
        ],
        'Superuser' => ['allowedToChangePasswords' => false], // able to reset any users password
    ],
    'OneTimePasswordAuthenticator' => [
        'checker' => \CakeDC\Auth\Authentication\DefaultOneTimePasswordAuthenticationChecker::class,
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
    'U2f' => [
        'enabled' => false,
        'checker' => \CakeDC\Auth\Authentication\DefaultU2fAuthenticationChecker::class,
    ],
    'Webauthn2fa' => [
        'enabled' => false,
        'appName' => null,//App must set a valid name here
        'id' => null,//default value is the current domain
        'checker' => \CakeDC\Auth\Authentication\DefaultWebauthn2fAuthenticationChecker::class,
    ],
    // default configuration used to auto-load the Auth Component, override to change the way Auth works
    'Auth' => [
        'Authentication' => [
            'serviceLoader' => \CakeDC\Users\Loader\AuthenticationServiceLoader::class
        ],
        'AuthenticationComponent' => [
            'load' => true,
            'loginRedirect' => '/',
            'requireIdentity' => false
        ],
        'Authenticators' => [
            'Session' => [
                'className' => 'Authentication.Session',
                'skipTwoFactorVerify' => true,
                'sessionKey' => 'Auth',
            ],
            'Form' => [
                'className' => 'CakeDC/Auth.Form',
                'urlChecker' => 'Authentication.CakeRouter',
            ],
            'Token' => [
                'className' => 'Authentication.Token',
                'skipTwoFactorVerify' => true,
                'header' => null,
                'queryParam' => 'api_key',
                'tokenPrefix' => null,
            ],
            'Cookie' => [
                'className' => 'CakeDC/Auth.Cookie',
                'skipTwoFactorVerify' => true,
                'rememberMeField' => 'remember_me',
                'cookie' => [
                    'expires' => new \DateTime('+1 month'),
                    'httponly' => true,
                ],
                'urlChecker' => 'Authentication.CakeRouter',
            ],
            'Social' => [
                'className' => 'CakeDC/Users.Social',
                'skipTwoFactorVerify' => true,
            ],
            'SocialPendingEmail' => [
                'className' => 'CakeDC/Users.SocialPendingEmail',
                'skipTwoFactorVerify' => true,
            ]
        ],
        'Identifiers' => [
            'Password' => [
                'className' => 'Authentication.Password',
                'fields' => [
                    'username' => ['username', 'email'],
                    'password' => 'password'
                ],
                'resolver' => [
                    'className' => 'Authentication.Orm',
                    'finder' => 'active'
                ],
            ],
            "Social" => [
                'className' => 'CakeDC/Users.Social',
                'authFinder' => 'active'
            ],
            'Token' => [
                'className' => 'Authentication.Token',
                'tokenField' => 'api_token',
                'resolver' => [
                    'className' => 'Authentication.Orm',
                    'finder' => 'active'
                ],
            ]
        ],
        "Authorization" => [
            'enable' => true,
            'serviceLoader' => \CakeDC\Users\Loader\AuthorizationServiceLoader::class
        ],
        'AuthorizationMiddleware' => [
            'unauthorizedHandler' => [
                'className' => 'CakeDC/Users.DefaultRedirect',
            ]
        ],
        'AuthorizationComponent' => [
            'enabled' => true,
        ],
        'RbacPolicy' => [],
        'PasswordRehash' => [
            'identifiers' => ['Password'],
        ]
    ],
    'OAuth' => [
        'providers' => [
            'facebook' => [
                'service' => 'CakeDC\Auth\Social\Service\OAuth2Service',
                'className' => 'League\OAuth2\Client\Provider\Facebook',
                'mapper' => 'CakeDC\Auth\Social\Mapper\Facebook',
                'authParams' => ['scope' => ['public_profile', 'email', 'user_birthday', 'user_gender', 'user_link']],
                'options' => [
                    'graphApiVersion' => 'v2.8', //bio field was deprecated on >= v2.8
                    'redirectUri' => Router::fullBaseUrl() . '/auth/facebook',
                    'linkSocialUri' => Router::fullBaseUrl() . '/link-social/facebook',
                    'callbackLinkSocialUri' => Router::fullBaseUrl() . '/callback-link-social/facebook',
                ]
            ],
            'twitter' => [
                'service' => 'CakeDC\Auth\Social\Service\OAuth1Service',
                'className' => 'League\OAuth1\Client\Server\Twitter',
                'mapper' => 'CakeDC\Auth\Social\Mapper\Twitter',
                'options' => [
                    'redirectUri' => Router::fullBaseUrl() . '/auth/twitter',
                    'linkSocialUri' => Router::fullBaseUrl() . '/link-social/twitter',
                    'callbackLinkSocialUri' => Router::fullBaseUrl() . '/callback-link-social/twitter',
                ]
            ],
            'linkedIn' => [
                'service' => 'CakeDC\Auth\Social\Service\OAuth2Service',
                'className' => 'League\OAuth2\Client\Provider\LinkedIn',
                'mapper' => 'CakeDC\Auth\Social\Mapper\LinkedIn',
                'options' => [
                    'redirectUri' => Router::fullBaseUrl() . '/auth/linkedIn',
                    'linkSocialUri' => Router::fullBaseUrl() . '/link-social/linkedIn',
                    'callbackLinkSocialUri' => Router::fullBaseUrl() . '/callback-link-social/linkedIn',
                ]
            ],
            'instagram' => [
                'service' => 'CakeDC\Auth\Social\Service\OAuth2Service',
                'className' => 'League\OAuth2\Client\Provider\Instagram',
                'mapper' => 'CakeDC\Auth\Social\Mapper\Instagram',
                'options' => [
                    'redirectUri' => Router::fullBaseUrl() . '/auth/instagram',
                    'linkSocialUri' => Router::fullBaseUrl() . '/link-social/instagram',
                    'callbackLinkSocialUri' => Router::fullBaseUrl() . '/callback-link-social/instagram',
                ]
            ],
            'google' => [
                'service' => 'CakeDC\Auth\Social\Service\OAuth2Service',
                'className' => 'League\OAuth2\Client\Provider\Google',
                'mapper' => 'CakeDC\Auth\Social\Mapper\Google',
                'options' => [
                    'userFields' => ['url', 'aboutMe'],
                    'redirectUri' => Router::fullBaseUrl() . '/auth/google',
                    'linkSocialUri' => Router::fullBaseUrl() . '/link-social/google',
                    'callbackLinkSocialUri' => Router::fullBaseUrl() . '/callback-link-social/google',
                ]
            ],
            'amazon' => [
                'service' => 'CakeDC\Auth\Social\Service\OAuth2Service',
                'className' => 'Luchianenco\OAuth2\Client\Provider\Amazon',
                'mapper' => 'CakeDC\Auth\Social\Mapper\Amazon',
                 'options' => [
                    'redirectUri' => Router::fullBaseUrl() . '/auth/amazon',
                    'linkSocialUri' => Router::fullBaseUrl() . '/link-social/amazon',
                    'callbackLinkSocialUri' => Router::fullBaseUrl() . '/callback-link-social/amazon',
                ]
            ],
            'cognito' => [
                'service' => 'CakeDC\Auth\Social\Service\OAuth2Service',
                'className' => 'CakeDC\OAuth2\Client\Provider\Cognito',
                'mapper' => 'CakeDC\Auth\Social\Mapper\Cognito',
                'options' => [
                    'redirectUri' => Router::fullBaseUrl() . '/auth/cognito',
                    'linkSocialUri' => Router::fullBaseUrl() . '/link-social/cognito',
                    'callbackLinkSocialUri' => Router::fullBaseUrl() . '/callback-link-social/cognito',
                    'scope' => 'email openid'
                ]
            ],
        ],
    ]
];

return $config;

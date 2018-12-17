<?php

/**
 * Copyright 2010 - 2018, Cake Development Corporation (https://www.cakedc.com)
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
    'OneTimePasswordAuthenticator' => [
        'checker' => \CakeDC\Auth\Authentication\DefaultTwoFactorAuthenticationChecker::class,
        'verifyAction' => [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'verify',
            'prefix' => false,
        ],
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
    'Auth' => [
        'Authentication' => [
            'serviceLoader' => \CakeDC\Users\Loader\AuthenticationServiceLoader::class
        ],
        'AuthenticationComponent' => [
            'load' => true,
            'loginAction' => [
                'plugin' => 'CakeDC/Users',
                'controller' => 'Users',
                'action' => 'login',
                'prefix' => false,
            ],
            'logoutRedirect' => [
                'plugin' => 'CakeDC/Users',
                'controller' => 'Users',
                'action' => 'login',
                'prefix' => false,
            ],
            'loginRedirect' => '/',
            'requireIdentity' => false
        ],
        'Authenticators' => [
            'Authentication.Session' => [
                'skipGoogleVerify' => true,
                'sessionKey' => 'Auth',
            ],
            'CakeDC/Auth.Form' => [
                'urlChecker' => 'Authentication.CakeRouter',
                'loginUrl' => [
                    'plugin' => 'CakeDC/Users',
                    'controller' => 'Users',
                    'action' => 'login',
                    'prefix' => false,
                ]
            ],
            'Authentication.Token' => [
                'skipGoogleVerify' => true,
                'header' => null,
                'queryParam' => 'api_key',
                'tokenPrefix' => null,
            ],
            'CakeDC/Auth.Cookie' => [
                'skipGoogleVerify' => true,
                'rememberMeField' => 'remember_me',
                'cookie' => [
                    'expires' => '1 month',
                    'httpOnly' => true,
                ],
                'urlChecker' => 'Authentication.CakeRouter',
                'loginUrl' => [
                    'plugin' => 'CakeDC/Users',
                    'controller' => 'Users',
                    'action' => 'login',
                    'prefix' => false,
                ]
            ],
            'CakeDC/Users.Social' => [
                'skipGoogleVerify' => true,
            ],
            'CakeDC/Users.SocialPendingEmail' => [
                'skipGoogleVerify' => true,
            ]
        ],
        'Identifiers' => [
            'Authentication.Password' => [],
            "CakeDC/Users.Social" => [
                'authFinder' => 'all'
            ],
            'Authentication.Token' => [
                'tokenField' => 'api_token'
            ]
        ],
        "Authorization" => [
            'enable' => true,
            'serviceLoader' => \CakeDC\Users\Loader\AuthorizationServiceLoader::class
        ],
        'AuthorizationMiddleware' => [
            'unauthorizedHandler' => [
                'exceptions' => [
                    'MissingIdentityException' => 'Authorization\Exception\MissingIdentityException',
                    'ForbiddenException' => 'Authorization\Exception\ForbiddenException',
                ],
                'className' => 'Authorization.CakeRedirect',
                'url' => [
                    'plugin' => 'CakeDC/Users',
                    'controller' => 'Users',
                    'action' => 'login',
                ]
            ]
        ],
        'AuthorizationComponent' => [
            'enabled' => true,
        ],
        'SocialLoginFailure' => [
            'component' => 'CakeDC/Users.Login',
            'defaultMessage' => __d('cake_d_c/users', 'Could not proceed with social account. Please try again'),
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
        'FormLoginFailure' => [
            'component' => 'CakeDC/Users.Login',
            'defaultMessage' => __d('cake_d_c/users', 'Username or password is incorrect'),
            'messages' => [
                'FAILURE_INVALID_RECAPTCHA' => __d('cake_d_c/users', 'Invalid reCaptcha'),
            ],
            'targetAuthenticator' => 'CakeDC\Auth\Authenticator\FormAuthenticator'
        ]
    ],
    'OAuth' => [
        'path' => ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'socialLogin', 'prefix' => null],
        'providers' => [
            'facebook' => [
                'service' => 'CakeDC\Auth\Social\Service\OAuth2Service',
                'className' => 'League\OAuth2\Client\Provider\Facebook',
                'mapper' => 'CakeDC\Auth\Social\Mapper\Facebook',
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
        ],
    ]
];

return $config;

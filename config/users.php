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
        'loginAction' => [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'login',
            'prefix' => false
        ],
        'authenticate' => [
            'all' => [
                'finder' => 'auth',
            ],
            'CakeDC/Auth.ApiKey',
            'CakeDC/Auth.RememberMe',
            'Form',
        ],
        'authorize' => [
            'CakeDC/Auth.Superuser',
            'CakeDC/Auth.SimpleRbac',
        ],
    ],
    'OAuth' => [
        'path' => ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'socialLogin', 'prefix' => null],
        'providers' => [
            'facebook' => [
                'className' => 'League\OAuth2\Client\Provider\Facebook',
                'options' => [
                    'graphApiVersion' => 'v2.8', //bio field was deprecated on >= v2.8
                    'redirectUri' => Router::fullBaseUrl() . '/auth/facebook',
                    'linkSocialUri' => Router::fullBaseUrl() . '/link-social/facebook',
                    'callbackLinkSocialUri' => Router::fullBaseUrl() . '/callback-link-social/facebook',
                ]
            ],
            'twitter' => [
                'options' => [
                    'redirectUri' => Router::fullBaseUrl() . '/auth/twitter',
                    'linkSocialUri' => Router::fullBaseUrl() . '/link-social/twitter',
                    'callbackLinkSocialUri' => Router::fullBaseUrl() . '/callback-link-social/twitter',
                ]
            ],
            'linkedIn' => [
                'className' => 'League\OAuth2\Client\Provider\LinkedIn',
                'options' => [
                    'redirectUri' => Router::fullBaseUrl() . '/auth/linkedIn',
                    'linkSocialUri' => Router::fullBaseUrl() . '/link-social/linkedIn',
                    'callbackLinkSocialUri' => Router::fullBaseUrl() . '/callback-link-social/linkedIn',
                ]
            ],
            'instagram' => [
                'className' => 'League\OAuth2\Client\Provider\Instagram',
                'options' => [
                    'redirectUri' => Router::fullBaseUrl() . '/auth/instagram',
                    'linkSocialUri' => Router::fullBaseUrl() . '/link-social/instagram',
                    'callbackLinkSocialUri' => Router::fullBaseUrl() . '/callback-link-social/instagram',
                ]
            ],
            'google' => [
                'className' => 'League\OAuth2\Client\Provider\Google',
                'options' => [
                    'userFields' => ['url', 'aboutMe'],
                    'redirectUri' => Router::fullBaseUrl() . '/auth/google',
                    'linkSocialUri' => Router::fullBaseUrl() . '/link-social/google',
                    'callbackLinkSocialUri' => Router::fullBaseUrl() . '/callback-link-social/google',
                ]
            ],
            'amazon' => [
                'className' => 'Luchianenco\OAuth2\Client\Provider\Amazon',
                'options' => [
                    'redirectUri' => Router::fullBaseUrl() . '/auth/amazon',
                    'linkSocialUri' => Router::fullBaseUrl() . '/link-social/amazon',
                    'callbackLinkSocialUri' => Router::fullBaseUrl() . '/callback-link-social/amazon',
                ]
            ],
        ],
    ],
    // Customize returned messages
    'Messages' => [
        'linkSocial' => [
            'successAssociate' => 'Social account was associated.',
            'failAssociate' => 'Could not associate account, please try again.',
            'errorToken' => "Error getting an access token. Error message: %s %s",
            'errorProfile' => "Error retrieving the authorized user's profile data. Error message: %s %s",
            'alreadyAssociated' => 'Social account already associated to another user'
        ],
        'login' => [
            'failSocialLogin' => 'Issues trying to log in with your social account',
            'missingEmail' => 'Please enter your email',
            'notValidated' => 'Your user has not been validated yet. Please check your inbox for instructions',
            'alreadyLoggedIn' => 'You are already logged in',
            'disabledGoogleAuthenticator' => 'Please enable Google Authenticator first.',
            'invalidVerificationCode' => 'Verification code is invalid. Try again',
            'invalidDetails' => 'Username or password is incorrect',
            'loggedOut' => 'You\'ve successfully logged out'
        ],
        'passwordManagement' => [
            'userNotFound' => 'User was not found',
            'failPasswordChange' => 'Password could not be changed',
            'passwordChanged' => 'Password has been changed successfully',
            'passwordResetEmail' => 'Please check your email to continue with password reset process',
            'failPasswordToken' => 'The password token could not be generated. Please try again',
            'userSpecifyNotFound' => 'User {0} was not found',
            'userNotActive' => 'The user is not active',
            'failTokenReset' => 'Token could not be reset',
            'googleAuthenticatorTokenReset' => 'Google Authenticator token was successfully reset'
        ],
        'profile' => [
            'userNotFound' => 'User was not found',
            'notAuthorized' => 'Not authorized, please login first'
        ],
        'register' => [
            'logoutRegister' => 'You must log out to register a new user account',
            'failSaveUser' => 'The user could not be saved',
            'invalidRecaptcha' => 'Invalid reCaptcha',
            'successRegister' => 'You have registered successfully, please log in',
            'validateBeforeLogin' => 'Please validate your account before log in'
        ],
        'simpleCrud' => [
            'saved' => 'The {0} has been saved',
            'failSave' => 'The {0} could not be saved',
            'deleted' => 'The {0} has been deleted',
            'failDelete' => 'The {0} could not be deleted'
        ],
        'social' => [
            'invalidRecaptcha' => 'The reCaptcha could not be validated'
        ],
        'userValidation' => [
            'accountValidated' => 'User account validated successfully',
            'failValidate' => 'User account could not be validated',
            'alreadyActive' => 'User already active',
            'tokenValidated' => 'Reset password token was validated successfully',
            'failTokenValidate' => 'Reset password token could not be validated',
            'invalidValidation' => 'Invalid validation type',
            'tokenOrUserExist' => 'Invalid token or user account already validated',
            'expiredToken' => 'Token already expired',
            'tokenReset' => 'Token has been reset successfully. Please check your email.',
            'failTokenReset' => 'Token could not be reset',
            'userNotFound' => 'User {0} was not found',
            'userAlreadyActive' => 'User {0} is already active'
        ],
        'socialAccounts' => [
            'accountValidated' => 'Account validated successfully',
            'failValidate' => 'Account could not be validated',
            'invalidToken' => 'Invalid token and/or social account',
            'socialAlreadyActive' => 'Social Account already active',
            'failSocialValidate' => 'Social Account could not be validated',
            'emailSent' => 'Email sent successfully',
            'failEmailSend' => 'Email could not be sent',
            'invalidAccount' => 'Invalid account',
            'failEmailResend' => 'Email could not be resent'
        ],
        'users' => [
            'passwordMistmatch' => 'Your password does not match your confirm password. Please try again',
            'usernameExists' => 'Username already exists',
            'emailExists' => 'Email already exists'
        ]
    ]
];

return $config;

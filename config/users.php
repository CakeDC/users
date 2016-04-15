<?php

/**
 * Copyright 2010 - 2015, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2015, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
use Cake\Core\Configure;
use Cake\Routing\Router;

$config = [
    'Users' => [
        //Table used to manage users
        'table' => 'CakeDC/Users.Users',
        //configure Auth component
        'auth' => true,
        //Password Hasher
        'passwordHasher' => '\Cake\Auth\DefaultPasswordHasher',
        //token expiration, 1 hour
        'Token' => ['expiration' => 3600],
        'Email' => [
            //determines if the user should include email
            'required' => true,
            //determines if registration workflow includes email validation
            'validate' => true,
        ],
        'Registration' => [
            //determines if the register is enabled
            'active' => true,
            //determines if the reCaptcha is enabled for registration
            'reCaptcha' => true,
        ],
        'reCaptcha' => [
            //reCaptcha key goes here
            'key' => null,
            //reCaptcha secret
            'secret' => null,
            //use reCaptcha in registration
            'registration' => false,
            //use reCaptcha in login, valid values are false, true
            'login' => false,
        ],
        'Tos' => [
            //determines if the user should include tos accepted
            'required' => true,
        ],
        'Social' => [
            //enable social login
            'login' => false,
        ],
        'Profile' => [
            //Allow view other users profiles
            'viewOthers' => true,
            'route' => ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'profile'],
        ],
        'Key' => [
            'Session' => [
                //session key to store the social auth data
                'social' => 'Users.social',
                //userId key used in reset password workflow
                'resetPasswordUserId' => 'Users.resetPasswordUserId',
            ],
            //form key to store the social auth data
            'Form' => [
                'social' => 'social'
            ],
            'Data' => [
                //data key to store the users email
                'email' => 'email',
                //data key to store email coming from social networks
                'socialEmail' => 'info.email',
                //data key to check if the remember me option is enabled
                'rememberMe' => 'remember_me',
            ],
        ],
        //Avatar placeholder
        'Avatar' => ['placeholder' => 'CakeDC/Users.avatar_placeholder.png'],
        'RememberMe' => [
            //configure Remember Me component
            'active' => true,
            'Cookie' => [
                'name' => 'remember_me',
                'Config' => [
                    'expires' => '1 month',
                    'httpOnly' => true,
                ]
            ]
        ],
    ],
//default configuration used to auto-load the Auth Component, override to change the way Auth works
    'Auth' => [
        'loginAction' => [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'login',
            'prefix' => false
        ],
        'authenticate' => [
            'all' => [
                'finder' => 'active',
            ],
            'CakeDC/Users.ApiKey',
            'CakeDC/Users.RememberMe',
            'Form',
        ],
        'authorize' => [
            'CakeDC/Users.Superuser',
            'CakeDC/Users.SimpleRbac',
        ],
    ],
    'OAuth' => [
        'path' => ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'socialLogin', 'prefix' => false],
        'providers' => [
            'facebook' => [
                'className' => 'League\OAuth2\Client\Provider\Facebook',
                'options' => [
                    'graphApiVersion' => 'v2.5',
                    'redirectUri' => Router::fullBaseUrl() . '/auth/facebook',
                ]
            ],
            'twitter' => [
                'options' => [
                    'redirectUri' => Router::fullBaseUrl() . '/auth/twitter',
                ]
            ],
            'linkedIn' => [
                'className' => 'League\OAuth2\Client\Provider\LinkedIn',
                'options' => [
                    'redirectUri' => Router::fullBaseUrl() . '/auth/linkedIn',
                ]
            ],
            'instagram' => [
                'className' => 'League\OAuth2\Client\Provider\Instagram',
                'options' => [
                    'redirectUri' => Router::fullBaseUrl() . '/auth/instagram',
                ]
            ],
            'google' => [
                'className' => 'League\OAuth2\Client\Provider\Google',
                'options' => [
                    'userFields' => ['url', 'aboutMe'],
                    'redirectUri' => Router::fullBaseUrl() . '/auth/google',
                ]
            ],
        ],
    ]
];

return $config;

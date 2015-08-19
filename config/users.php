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

$config = [
    'Users' => [
        //Table used to manage users
        'table' => 'Users.Users',
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
        'Tos' => [
            //determines if the user should include tos accepted
            'required' => true,
        ],
        'Social' => [
            //enable social login
            'login' => true,
        ],
        'Profile' => [
            //Allow view other users profiles
            'viewOthers' => true,
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
        'Avatar' => ['placeholder' => 'Users.avatar_placeholder.png'],
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
            'plugin' => 'Users',
            'controller' => 'Users',
            'action' => 'login',
        ],
        'authenticate' => [
            'all' => [
                'scope' => ['active' => 1]
            ],
            'Users.RememberMe',
            'Form',
        ],
        'authorize' => [
            'Users.Superuser',
            'Users.SimpleRbac',
        ],
    ],
//default Opauth configuration, you'll need to provide the strategy keys
    'Opauth' => [
        'path' => ['plugin' => 'Users', 'controller' => 'Users', 'action' => 'opauthInit'],
        'callback_param' => 'callback',
        'complete_url' => ['admin' => false, 'plugin' => 'Users', 'controller' => 'Users', 'action' => 'login'],
        'Strategy' => [
            'Facebook' => [
                'scope' => ['public_profile', 'user_friends', 'email']
            ],
            'Twitter' => [
                'curl_cainfo' => false,
                'curl_capath' => false
            ]
        ]
    ]
];

return $config;

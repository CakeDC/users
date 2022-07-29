<?php
declare(strict_types=1);

/**
 * Abstract schema for CakePHP tests.
 *
 * This format resembles the existing fixture schema
 * and is converted to SQL via the Schema generation
 * features of the Database package.
 */
return [
    [
        'table' => 'posts',
        'columns' => [
            'id' => ['type' => 'uuid', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
            'title' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
            'user_id' => ['type' => 'uuid', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
        ],
    ],
    [
        'table' => 'social_accounts',
        'columns' => [
            'id' => ['type' => 'uuid', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
            'user_id' => ['type' => 'uuid', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
            'provider' => ['type' => 'string', 'length' => 255, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
            'username' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
            'reference' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
            'avatar' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
            'description' => ['type' => 'text', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
            'link' => ['type' => 'text', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
            'token' => ['type' => 'string', 'length' => 500, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
            'token_secret' => ['type' => 'string', 'length' => 500, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
            'token_expires' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
            'active' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => true, 'comment' => '', 'precision' => null],
            'data' => ['type' => 'text', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
            'created' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
            'modified' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        ],
        'constraints' => [
            'primary' => [
                'type' => 'primary',
                'columns' => [
                    'id',
                ],
            ],
        ],
    ],
    [
        'table' => 'users',
        'columns' => [
            'id' => ['type' => 'uuid', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
            'username' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
            'email' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
            'password' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
            'first_name' => ['type' => 'string', 'length' => 50, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
            'last_name' => ['type' => 'string', 'length' => 50, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
            'token' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
            'token_expires' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
            'api_token' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
            'activation_date' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
            'secret' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
            'secret_verified' => ['type' => 'boolean', 'length' => null, 'null' => true, 'default' => false, 'comment' => '', 'precision' => null],
            'tos_date' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
            'active' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => true, 'comment' => '', 'precision' => null],
            'is_superuser' => ['type' => 'boolean', 'length' => null, 'unsigned' => false, 'null' => false, 'default' => false, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
            'role' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => 'user', 'comment' => '', 'precision' => null, 'fixed' => null],
            'created' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
            'modified' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
            'additional_data' => ['type' => 'text', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
            'last_login' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        ],
        'constraints' => [
            'primary' => [
                'type' => 'primary',
                'columns' => [
                    'id',
                ],
            ],
        ],
    ],
    [
        'table' => 'posts_users',
        'columns' => [
            'id' => ['type' => 'uuid', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
            'user_id' => ['type' => 'uuid', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
            'post_id' => ['type' => 'uuid', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
        ],
    ],
    [
        'table' => 'otp_codes',
        'columns' => [
            'id' => ['type' => 'integer', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
            'user_id' => ['type' => 'uuid', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
            'code' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
            'tries' => ['type' => 'integer', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
            'validated' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
            'created' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
        ],
    ],
];

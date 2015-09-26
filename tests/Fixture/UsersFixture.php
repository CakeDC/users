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

namespace CakeDC\Users\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * UsersFixture
 *
 */
class UsersFixture extends TestFixture
{

    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
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
        'tos_date' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'active' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => true, 'comment' => '', 'precision' => null],
        'is_superuser' => ['type' => 'boolean', 'length' => null, 'unsigned' => false, 'null' => false, 'default' => false, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'role' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => 'user', 'comment' => '', 'precision' => null, 'fixed' => null],
        'created' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'modified' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
        ],
        '_options' => [
            'engine' => 'InnoDB',
            'collation' => 'utf8_general_ci'
        ],
    ];
    // @codingStandardsIgnoreEnd

    /**
     * Records
     *
     * @var array
     */
    public $records = [
        [
            'id' => '00000000-0000-0000-0000-000000000001',
            'username' => 'user-1',
            'email' => 'user-1@test.com',
            'password' => '12345',
            'first_name' => 'first1',
            'last_name' => 'last1',
            'token' => 'ae93ddbe32664ce7927cf0c5c5a5e59d',
            'token_expires' => '2035-06-24 17:33:54',
            'api_token' => 'yyy',
            'activation_date' => '2015-06-24 17:33:54',
            'tos_date' => '2015-06-24 17:33:54',
            'active' => false,
            'is_superuser' => true,
            'role' => 'admin',
            'created' => '2015-06-24 17:33:54',
            'modified' => '2015-06-24 17:33:54'
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000002',
            'username' => 'user-2',
            'email' => 'user-2@test.com',
            'password' => '12345',
            'first_name' => 'user',
            'last_name' => 'second',
            'token' => '6614f65816754310a5f0553436dd89e9',
            'token_expires' => '2015-06-24 17:33:54',
            'api_token' => 'xxx',
            'activation_date' => '2015-06-24 17:33:54',
            'tos_date' => '2015-06-24 17:33:54',
            'active' => true,
            'is_superuser' => true,
            'role' => 'admin',
            'created' => '2015-06-24 17:33:54',
            'modified' => '2015-06-24 17:33:54'
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000003',
            'username' => 'user-3',
            'email' => 'user-3@test.com',
            'password' => '12345',
            'first_name' => 'user',
            'last_name' => 'third',
            'token' => 'token-3',
            'token_expires' => '2030-06-20 17:33:54',
            'api_token' => 'xxx',
            'activation_date' => '2015-06-24 17:33:54',
            'tos_date' => '2015-06-24 17:33:54',
            'active' => false,
            'is_superuser' => true,
            'role' => 'admin',
            'created' => '2015-06-24 17:33:54',
            'modified' => '2015-06-24 17:33:54'
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000004',
            'username' => 'user-4',
            'email' => '4@example.com',
            'password' => 'Lorem ipsum dolor sit amet',
            'first_name' => 'FirstName4',
            'last_name' => 'Lorem ipsum dolor sit amet',
            'token' => 'token-4',
            'token_expires' => '2030-06-24 17:33:54',
            'api_token' => 'Lorem ipsum dolor sit amet',
            'activation_date' => '2015-06-24 17:33:54',
            'tos_date' => '2015-06-24 17:33:54',
            'active' => true,
            'is_superuser' => false,
            'role' => 'Lorem ipsum dolor sit amet',
            'created' => '2015-06-24 17:33:54',
            'modified' => '2015-06-24 17:33:54'
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000005',
            'username' => 'user-5',
            'email' => 'test@example.com',
            'password' => '12345',
            'first_name' => 'first-user-5',
            'last_name' => 'firts name 5',
            'token' => 'token-5',
            'token_expires' => '2015-06-24 17:33:54',
            'api_token' => '',
            'activation_date' => '2015-06-24 17:33:54',
            'tos_date' => '2015-06-24 17:33:54',
            'active' => true,
            'is_superuser' => false,
            'role' => 'user',
            'created' => '2015-06-24 17:33:54',
            'modified' => '2015-06-24 17:33:54'
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000006',
            'username' => 'Lorem ipsum dolor sit amet',
            'email' => 'Lorem ipsum dolor sit amet',
            'password' => 'Lorem ipsum dolor sit amet',
            'first_name' => 'Lorem ipsum dolor sit amet',
            'last_name' => 'Lorem ipsum dolor sit amet',
            'token' => 'Lorem ipsum dolor sit amet',
            'token_expires' => '2015-06-24 17:33:54',
            'api_token' => 'Lorem ipsum dolor sit amet',
            'activation_date' => '2015-06-24 17:33:54',
            'tos_date' => '2015-06-24 17:33:54',
            'active' => true,
            'is_superuser' => false,
            'role' => 'Lorem ipsum dolor sit amet',
            'created' => '2015-06-24 17:33:54',
            'modified' => '2015-06-24 17:33:54'
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000007',
            'username' => 'Lorem ipsum dolor sit amet',
            'email' => 'Lorem ipsum dolor sit amet',
            'password' => 'Lorem ipsum dolor sit amet',
            'first_name' => 'Lorem ipsum dolor sit amet',
            'last_name' => 'Lorem ipsum dolor sit amet',
            'token' => 'Lorem ipsum dolor sit amet',
            'token_expires' => '2015-06-24 17:33:54',
            'api_token' => 'Lorem ipsum dolor sit amet',
            'activation_date' => '2015-06-24 17:33:54',
            'tos_date' => '2015-06-24 17:33:54',
            'active' => true,
            'is_superuser' => false,
            'role' => 'Lorem ipsum dolor sit amet',
            'created' => '2015-06-24 17:33:54',
            'modified' => '2015-06-24 17:33:54'
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000008',
            'username' => 'Lorem ipsum dolor sit amet',
            'email' => 'Lorem ipsum dolor sit amet',
            'password' => 'Lorem ipsum dolor sit amet',
            'first_name' => 'Lorem ipsum dolor sit amet',
            'last_name' => 'Lorem ipsum dolor sit amet',
            'token' => 'Lorem ipsum dolor sit amet',
            'token_expires' => '2015-06-24 17:33:54',
            'api_token' => 'Lorem ipsum dolor sit amet',
            'activation_date' => '2015-06-24 17:33:54',
            'tos_date' => '2015-06-24 17:33:54',
            'active' => true,
            'is_superuser' => false,
            'role' => 'Lorem ipsum dolor sit amet',
            'created' => '2015-06-24 17:33:54',
            'modified' => '2015-06-24 17:33:54'
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000009',
            'username' => 'Lorem ipsum dolor sit amet',
            'email' => 'Lorem ipsum dolor sit amet',
            'password' => 'Lorem ipsum dolor sit amet',
            'first_name' => 'Lorem ipsum dolor sit amet',
            'last_name' => 'Lorem ipsum dolor sit amet',
            'token' => 'Lorem ipsum dolor sit amet',
            'token_expires' => '2015-06-24 17:33:54',
            'api_token' => 'Lorem ipsum dolor sit amet',
            'activation_date' => '2015-06-24 17:33:54',
            'tos_date' => '2015-06-24 17:33:54',
            'active' => true,
            'is_superuser' => false,
            'role' => 'Lorem ipsum dolor sit amet',
            'created' => '2015-06-24 17:33:54',
            'modified' => '2015-06-24 17:33:54'
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000010',
            'username' => 'Lorem ipsum dolor sit amet',
            'email' => 'Lorem ipsum dolor sit amet',
            'password' => 'Lorem ipsum dolor sit amet',
            'first_name' => 'Lorem ipsum dolor sit amet',
            'last_name' => 'Lorem ipsum dolor sit amet',
            'token' => 'Lorem ipsum dolor sit amet',
            'token_expires' => '2015-06-24 17:33:54',
            'api_token' => 'Lorem ipsum dolor sit amet',
            'activation_date' => '2015-06-24 17:33:54',
            'tos_date' => '2015-06-24 17:33:54',
            'active' => true,
            'is_superuser' => false,
            'role' => 'Lorem ipsum dolor sit amet',
            'created' => '2015-06-24 17:33:54',
            'modified' => '2015-06-24 17:33:54'
        ],
    ];
}

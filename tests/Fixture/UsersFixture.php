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

namespace Users\Test\Fixture;

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
        'active' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '0', 'comment' => '', 'precision' => null],
        'is_superuser' => ['type' => 'integer', 'length' => 1, 'unsigned' => false, 'null' => false, 'default' => '0', 'comment' => '', 'precision' => null, 'autoIncrement' => null],
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
            'id' => 1,
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
            'active' => 0,
            'is_superuser' => 1,
            'role' => 'admin',
            'created' => '2015-06-24 17:33:54',
            'modified' => '2015-06-24 17:33:54'
        ],
        [
            'id' => 2,
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
            'active' => 1,
            'is_superuser' => 1,
            'role' => 'admin',
            'created' => '2015-06-24 17:33:54',
            'modified' => '2015-06-24 17:33:54'
        ],
        [
            'id' => 3,
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
            'active' => 0,
            'is_superuser' => 1,
            'role' => 'admin',
            'created' => '2015-06-24 17:33:54',
            'modified' => '2015-06-24 17:33:54'
        ],
        [
            'id' => 4,
            'username' => 'user-4',
            'email' => '4@example.com',
            'password' => 'Lorem ipsum dolor sit amet',
            'first_name' => 'FirstName4',
            'last_name' => 'Lorem ipsum dolor sit amet',
            'token' => 'token-4',
            'token_expires' => '2015-06-24 17:33:54',
            'api_token' => 'Lorem ipsum dolor sit amet',
            'activation_date' => '2015-06-24 17:33:54',
            'tos_date' => '2015-06-24 17:33:54',
            'active' => 1,
            'is_superuser' => 4,
            'role' => 'Lorem ipsum dolor sit amet',
            'created' => '2015-06-24 17:33:54',
            'modified' => '2015-06-24 17:33:54'
        ],
        [
            'id' => 5,
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
            'active' => 0,
            'is_superuser' => 0,
            'role' => 'user',
            'created' => '2015-06-24 17:33:54',
            'modified' => '2015-06-24 17:33:54'
        ],
        [
            'id' => 6,
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
            'active' => 1,
            'is_superuser' => 6,
            'role' => 'Lorem ipsum dolor sit amet',
            'created' => '2015-06-24 17:33:54',
            'modified' => '2015-06-24 17:33:54'
        ],
        [
            'id' => 7,
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
            'active' => 1,
            'is_superuser' => 7,
            'role' => 'Lorem ipsum dolor sit amet',
            'created' => '2015-06-24 17:33:54',
            'modified' => '2015-06-24 17:33:54'
        ],
        [
            'id' => 8,
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
            'active' => 1,
            'is_superuser' => 8,
            'role' => 'Lorem ipsum dolor sit amet',
            'created' => '2015-06-24 17:33:54',
            'modified' => '2015-06-24 17:33:54'
        ],
        [
            'id' => 9,
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
            'active' => 1,
            'is_superuser' => 9,
            'role' => 'Lorem ipsum dolor sit amet',
            'created' => '2015-06-24 17:33:54',
            'modified' => '2015-06-24 17:33:54'
        ],
        [
            'id' => 10,
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
            'active' => 1,
            'is_superuser' => 10,
            'role' => 'Lorem ipsum dolor sit amet',
            'created' => '2015-06-24 17:33:54',
            'modified' => '2015-06-24 17:33:54'
        ],
    ];
}

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
            'token' => '926029fede5b4a28bce4ad5853c43a25',
            'token_expires' => '2015-06-20 17:33:54',
            'api_token' => 'xxx',
            'activation_date' => '2015-06-24 17:33:54',
            'tos_date' => '2015-06-24 17:33:54',
            'active' => 0,
            'is_superuser' => 1,
            'role' => 'admin',
            'created' => '2015-06-24 17:33:54',
            'modified' => '2015-06-24 17:33:54'
        ],
    ];
}

<?php
namespace CakeDC\Users\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * AccountsFixture
 *
 */
class SocialAccountsFixture extends TestFixture
{

    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'uuid', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'user_id' => ['type' => 'string', 'length' => 36, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
        'provider' => ['type' => 'string', 'length' => 255, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'username' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
        'reference' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
        'avatar' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
        'description' => ['type' => 'text', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'token' => ['type' => 'string', 'length' => 500, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
        'token_secret' => ['type' => 'string', 'length' => 500, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
        'token_expires' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'active' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '1', 'comment' => '', 'precision' => null],
        'data' => ['type' => 'text', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
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
            'user_id' => 1,
            'provider' => 'Facebook',
            'username' => 'user-1-fb',
            'reference' => 'reference-1-1234',
            'avatar' => 'Lorem ipsum dolor sit amet',
            'description' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
            'token' => 'token-1234',
            'token_secret' => 'Lorem ipsum dolor sit amet',
            'token_expires' => '2015-05-22 21:52:44',
            'active' => 0,
            'data' => '',
            'created' => '2015-05-22 21:52:44',
            'modified' => '2015-05-22 21:52:44'
        ],
        [
            'id' => 2,
            'user_id' => 1,
            'provider' => 'Twitter',
            'username' => 'user-1-tw',
            'reference' => 'reference-1-1234',
            'avatar' => 'Lorem ipsum dolor sit amet',
            'description' => '',
            'token' => 'token-1234',
            'token_secret' => 'Lorem ipsum dolor sit amet',
            'token_expires' => '2015-05-22 21:52:44',
            'active' => 1,
            'data' => '',
            'created' => '2015-05-22 21:52:44',
            'modified' => '2015-05-22 21:52:44'
        ],
        [
            'id' => 3,
            'user_id' => 2,
            'provider' => 'Facebook',
            'username' => 'user-2-fb',
            'reference' => 'reference-2-1',
            'avatar' => 'Lorem ipsum dolor sit amet',
            'description' => '',
            'token' => 'token-reference-2-1',
            'token_secret' => 'Lorem ipsum dolor sit amet',
            'token_expires' => '2015-05-22 21:52:44',
            'active' => 1,
            'data' => '',
            'created' => '2015-05-22 21:52:44',
            'modified' => '2015-05-22 21:52:44'
        ],
        [
            'id' => 4,
            'user_id' => 3,
            'provider' => 'Twitter',
            'username' => 'user-2-tw',
            'reference' => 'reference-2-2',
            'avatar' => 'Lorem ipsum dolor sit amet',
            'description' => '',
            'token' => 'token-reference-2-2',
            'token_secret' => 'Lorem ipsum dolor sit amet',
            'token_expires' => '2015-05-22 21:52:44',
            'active' => 0,
            'data' => '',
            'created' => '2015-05-22 21:52:44',
            'modified' => '2015-05-22 21:52:44'
        ],
        [
            'id' => 5,
            'user_id' => 4,
            'provider' => 'Twitter',
            'username' => 'user-2-tw',
            'reference' => 'reference-2-2',
            'avatar' => 'Lorem ipsum dolor sit amet',
            'description' => '',
            'token' => 'token-reference-2-2',
            'token_secret' => 'Lorem ipsum dolor sit amet',
            'token_expires' => '2015-05-22 21:52:44',
            'active' => 0,
            'data' => '',
            'created' => '2015-05-22 21:52:44',
            'modified' => '2015-05-22 21:52:44'
        ],
    ];
}

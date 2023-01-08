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

namespace CakeDC\Users\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * AccountsFixture
 */
class SocialAccountsFixture extends TestFixture
{
    /**
     * Records
     *
     * @var array
     */
    public array $records = [
        [
            'id' => '00000000-0000-0000-0000-000000000001',
            'user_id' => '00000000-0000-0000-0000-000000000001',
            'provider' => 'Facebook',
            'username' => 'user-1-fb',
            'reference' => 'reference-1-1234',
            'avatar' => 'Lorem ipsum dolor sit amet',
            'description' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
            'token' => 'token-1234',
            'token_secret' => 'Lorem ipsum dolor sit amet',
            'token_expires' => '2015-05-22 21:52:44',
            'active' => false,
            'data' => '',
            'created' => '2015-05-22 21:52:44',
            'modified' => '2015-05-22 21:52:44',
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000002',
            'user_id' => '00000000-0000-0000-0000-000000000001',
            'provider' => 'Twitter',
            'username' => 'user-1-tw',
            'reference' => 'reference-1-1234',
            'avatar' => 'Lorem ipsum dolor sit amet',
            'description' => '',
            'token' => 'token-1234',
            'token_secret' => 'Lorem ipsum dolor sit amet',
            'token_expires' => '2015-05-22 21:52:44',
            'active' => true,
            'data' => '',
            'created' => '2015-05-22 21:52:44',
            'modified' => '2015-05-22 21:52:44',
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000003',
            'user_id' => '00000000-0000-0000-0000-000000000002',
            'provider' => 'Facebook',
            'username' => 'user-2-fb',
            'reference' => 'reference-2-1',
            'avatar' => 'Lorem ipsum dolor sit amet',
            'description' => '',
            'token' => 'token-reference-2-1',
            'token_secret' => 'Lorem ipsum dolor sit amet',
            'token_expires' => '2015-05-22 21:52:44',
            'active' => true,
            'data' => '',
            'created' => '2015-05-22 21:52:44',
            'modified' => '2015-05-22 21:52:44',
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000004',
            'user_id' => '00000000-0000-0000-0000-000000000003',
            'provider' => 'Twitter',
            'username' => 'user-2-tw',
            'reference' => 'reference-2-2',
            'avatar' => 'Lorem ipsum dolor sit amet',
            'description' => '',
            'token' => 'token-reference-2-2',
            'token_secret' => 'Lorem ipsum dolor sit amet',
            'token_expires' => '2015-05-22 21:52:44',
            'active' => false,
            'data' => '',
            'created' => '2015-05-22 21:52:44',
            'modified' => '2015-05-22 21:52:44',
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000005',
            'user_id' => '00000000-0000-0000-0000-000000000004',
            'provider' => 'Twitter',
            'username' => 'user-2-tw',
            'reference' => 'reference-2-2',
            'avatar' => 'Lorem ipsum dolor sit amet',
            'description' => '',
            'token' => 'token-reference-2-2',
            'token_secret' => 'Lorem ipsum dolor sit amet',
            'token_expires' => '2015-05-22 21:52:44',
            'active' => false,
            'data' => '',
            'created' => '2015-05-22 21:52:44',
            'modified' => '2015-05-22 21:52:44',
        ],
    ];
}

<?php
declare(strict_types=1);

namespace CakeDC\Users\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * FailedPasswordAttemptsFixture
 */
class FailedPasswordAttemptsFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {

        $this->records = [
            [
                'id' => '79cdd7a7-0f34-49dd-a691-21444f94c701',
                'user_id' => '00000000-0000-0000-0000-000000000002',
                'created' => date('Y-m-d H:i:s', strtotime('-20 minutes')),
            ],
            [
                'id' => '79cdd7a7-0f34-49dd-a691-21444f94c702',
                'user_id' => '00000000-0000-0000-0000-000000000002',
                'created' => date('Y-m-d H:i:s', strtotime('-4 minutes')),
            ],
            [
                'id' => '79cdd7a7-0f34-49dd-a691-21444f94c703',
                'user_id' => '00000000-0000-0000-0000-000000000002',
                'created' => date('Y-m-d H:i:s', strtotime('-4 minutes')),
            ],
            [
                'id' => '79cdd7a7-0f34-49dd-a691-21444f94c704',
                'user_id' => '00000000-0000-0000-0000-000000000002',
                'created' => date('Y-m-d H:i:s', strtotime('-3 minutes')),
            ],
            [
                'id' => '79cdd7a7-0f34-49dd-a691-21444f94c705',
                'user_id' => '00000000-0000-0000-0000-000000000002',
                'created' => date('Y-m-d H:i:s', strtotime('-2 minutes')),
            ],
            [
                'id' => '79cdd7a7-0f34-49dd-a691-21444f94c800',
                'user_id' => '00000000-0000-0000-0000-000000000004',
                'created' => date('Y-m-d H:i:s', strtotime('-4 minutes')),
            ],
            [
                'id' => '79cdd7a7-0f34-49dd-a691-21444f94c801',
                'user_id' => '00000000-0000-0000-0000-000000000004',
                'created' => date('Y-m-d H:i:s', strtotime('-4 minutes')),
            ],
            [
                'id' => '79cdd7a7-0f34-49dd-a691-21444f94c802',
                'user_id' => '00000000-0000-0000-0000-000000000004',
                'created' => date('Y-m-d H:i:s', strtotime('-4 minutes')),
            ],
            [
                'id' => '79cdd7a7-0f34-49dd-a691-21444f94c803',
                'user_id' => '00000000-0000-0000-0000-000000000004',
                'created' => date('Y-m-d H:i:s', strtotime('-3 minutes')),
            ],
            [
                'id' => '79cdd7a7-0f34-49dd-a691-21444f94c804',
                'user_id' => '00000000-0000-0000-0000-000000000004',
                'created' => date('Y-m-d H:i:s', strtotime('-3 minutes')),
            ],
            [
                'id' => '79cdd7a7-0f34-49dd-a691-21444f94c805',
                'user_id' => '00000000-0000-0000-0000-000000000004',
                'created' => date('Y-m-d H:i:s', strtotime('-2 minutes')),
            ],
        ];
        parent::init();
    }
}

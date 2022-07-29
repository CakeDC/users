<?php
declare(strict_types=1);

namespace CakeDC\Users\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * OtpCodesFixture
 */
class OtpCodesFixture extends TestFixture
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
                'id' => 1,
                'user_id' => '00000000-0000-0000-0000-000000000001',
                'code' => '000001',
                'tries' => 1,
                'validated' => '2022-02-22 17:13:19',
                'created' => '2022-02-22 17:13:19',
            ],
            [
                'id' => 2,
                'user_id' => '00000000-0000-0000-0000-000000000002',
                'code' => '000002',
                'tries' => 1,
                'validated' => null,
                'created' => '2022-02-22 17:13:19',
            ],
        ];
        parent::init();
    }
}

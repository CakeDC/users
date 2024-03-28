<?php
declare(strict_types=1);

namespace CakeDC\Users\Test\TestCase\Model\Table;

use Cake\TestSuite\TestCase;
use CakeDC\Users\Model\Table\FailedPasswordAttemptsTable;

/**
 * CakeDC\Users\Model\Table\FailedPasswordAttemptsTable Test Case
 */
class FailedPasswordAttemptsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \CakeDC\Users\Model\Table\FailedPasswordAttemptsTable
     */
    protected $FailedPasswordAttempts;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'plugin.CakeDC/Users.FailedPasswordAttempts',
        'plugin.CakeDC/Users.Users',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('FailedPasswordAttempts') ? [] : ['className' => FailedPasswordAttemptsTable::class];
        $this->FailedPasswordAttempts = $this->getTableLocator()->get('FailedPasswordAttempts', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->FailedPasswordAttempts);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \CakeDC\Users\Model\Table\FailedPasswordAttemptsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @uses \CakeDC\Users\Model\Table\FailedPasswordAttemptsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}

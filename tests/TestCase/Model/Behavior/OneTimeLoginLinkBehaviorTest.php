<?php
declare(strict_types=1);

namespace CakeDC\Users\Test\TestCase\Model\Behavior;

use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\I18n\DateTime;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CakeDC\Users\Model\Behavior\OneTimeLoginLinkBehavior;
use OutOfBoundsException;

/**
 * Mock Delivery Handler
 */
class MockDeliveryHandler
{
    public static $sent = [];

    public function __construct(array $options = [])
    {
    }

    public function send(EntityInterface $user, string $token): void
    {
        static::$sent[] = ['user' => $user, 'token' => $token];
    }
}

/**
 * OneTimeLoginLinkBehavior Test Case
 */
class OneTimeLoginLinkBehaviorTest extends TestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    protected array $fixtures = [
        'plugin.CakeDC/Users.Users',
    ];

    /**
     * Table
     */
    protected $table;

    /**
     * Behavior
     */
    protected $Behavior;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->table = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $this->table->addBehavior('CakeDC/Users.OneTimeLoginLink');
        $this->Behavior = $this->table->getBehavior('OneTimeLoginLink');
        MockDeliveryHandler::$sent = [];
        Configure::write('OneTimeLogin.DeliveryHandlers', [
            'mock' => [
                'className' => MockDeliveryHandler::class,
            ],
        ]);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->table, $this->Behavior);
        parent::tearDown();
    }

    /**
     * Test sendLoginLink
     *
     * @return void
     */
    public function testSendLoginLink(): void
    {
        $this->Behavior->sendLoginLink('user-1');
        $user = $this->table->findByUsername('user-1')->first();
        $this->assertNotEmpty($user->login_token);
        $this->assertNotNull($user->login_token_date);
        $this->assertFalse($user->token_send_requested);
        $this->assertCount(1, MockDeliveryHandler::$sent);
        $this->assertEquals($user->id, MockDeliveryHandler::$sent[0]['user']->id);
        $this->assertEquals($user->login_token, MockDeliveryHandler::$sent[0]['token']);
    }

    /**
     * Test sendLoginLink with email
     *
     * @return void
     */
    public function testSendLoginLinkWithEmail(): void
    {
        $this->Behavior->sendLoginLink('user-1@test.com');
        $user = $this->table->findByEmail('user-1@test.com')->first();
        $this->assertNotEmpty($user->login_token);
        $this->assertCount(1, MockDeliveryHandler::$sent);
    }

    /**
     * Test sendLoginLink user not found
     *
     * @return void
     */
    public function testSendLoginLinkUserNotFound(): void
    {
        $this->expectException(RecordNotFoundException::class);
        $this->Behavior->sendLoginLink('non-existent');
    }

    /**
     * Test sendLoginLink too soon
     *
     * @return void
     */
    public function testSendLoginLinkTooSoon(): void
    {
        $this->Behavior->sendLoginLink('user-1');
        $this->assertCount(1, MockDeliveryHandler::$sent);
        
        $this->Behavior->sendLoginLink('user-1');
        $user = $this->table->findByUsername('user-1')->first();
        $this->assertTrue((bool)$user->token_send_requested);
        $this->assertCount(1, MockDeliveryHandler::$sent); // Should not send again
    }

    /**
     * Test loginWithToken
     *
     * @return void
     */
    public function testLoginWithToken(): void
    {
        $this->Behavior->sendLoginLink('user-1');
        $user = $this->table->findByUsername('user-1')->first();
        $token = $user->login_token;

        $loggedUser = $this->Behavior->loginWithToken($token);
        $this->assertNotNull($loggedUser);
        $this->assertEquals($user->id, $loggedUser->id);

        $userAfter = $this->table->get($user->id);
        $this->assertNull($userAfter->login_token);
    }

    /**
     * Test loginWithToken expired
     *
     * @return void
     */
    public function testLoginWithTokenExpired(): void
    {
        $this->Behavior->sendLoginLink('user-1');
        $user = $this->table->findByUsername('user-1')->first();
        $token = $user->login_token;

        // Mock expired token
        $this->table->updateAll(
            ['login_token_date' => DateTime::now()->subSeconds(700)],
            ['id' => $user->id]
        );

        Configure::write('Auth.OneTimeLogin.tokenLifeTime', 600);
        $loggedUser = $this->Behavior->loginWithToken($token);
        $this->assertNull($loggedUser);
    }

    /**
     * Test loginWithToken invalid
     *
     * @return void
     */
    public function testLoginWithTokenInvalid(): void
    {
        $loggedUser = $this->Behavior->loginWithToken('invalid-token');
        $this->assertNull($loggedUser);
    }

    /**
     * Test requestTokenSend
     *
     * @return void
     */
    public function testRequestTokenSend(): void
    {
        $this->Behavior->requestTokenSend('user-1');
        $user = $this->table->findByUsername('user-1')->first();
        $this->assertTrue((bool)$user->token_send_requested);
    }

    /**
     * Test findByUsernameOrEmail missing username
     *
     * @return void
     */
    public function testFindByUsernameOrEmailMissingUsername(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->table->find('byUsernameOrEmail', [])->first();
    }

    /**
     * Test findByOneTimeToken missing token
     *
     * @return void
     */
    public function testFindByOneTimeTokenMissingToken(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->table->find('byOneTimeToken', [])->first();
    }
}

<?php
declare(strict_types=1);

namespace CakeDC\Users\Test\TestCase\Webauthn;

use Cake\Core\Configure;
use Cake\Http\ServerRequestFactory;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CakeDC\Users\Webauthn\AuthenticateAdapter;
use Webauthn\PublicKeyCredentialRequestOptions;

class AuthenticateAdapterTest extends TestCase
{
    protected array $fixtures = [
        'plugin.CakeDC/Users.Users',
        'plugin.CakeDC/Users.SocialAccounts',
    ];

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        Configure::write('Webauthn2fa.appName', 'ACME Webauthn Server');
        Configure::write('Webauthn2fa.id', 'localhost');
    }

    /**
     * Test getRegisterOptions method
     *
     * @return void
     */
    public function testGetOptions()
    {
        $userId = '00000000-0000-0000-0000-000000000001';
        $UsersTable = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $user = $UsersTable->get($userId);
        $request = ServerRequestFactory::fromGlobals();
        $request->getSession()->write('Webauthn2fa.User', $user);
        $adapter = new AuthenticateAdapter($request);
        $options = $adapter->getOptions();
        $this->assertInstanceOf(PublicKeyCredentialRequestOptions::class, $options);
        $this->assertSame($options, $request->getSession()->read('Webauthn2fa.authenticateOptions'));
    }
}

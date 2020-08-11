<?php
declare(strict_types=1);

/**
 * Copyright 2010 - 2019, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2018, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Test\TestCase\Controller\Traits;

use Cake\Core\Configure;
use Cake\Http\ServerRequest;
use Cake\ORM\TableRegistry;
use CakeDC\Auth\Controller\Component\OneTimePasswordAuthenticatorComponent;

class OneTimePasswordVerifyTraitTest extends BaseTraitTest
{
    protected $loginPage = [
        'plugin' => 'CakeDC/Users',
        'prefix' => false,
        'controller' => 'users',
        'action' => 'login',
    ];

    /**
     * setup
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->traitClassName = 'CakeDC\Users\Controller\UsersController';
        $this->traitMockMethods = ['dispatchEvent', 'isStopped', 'redirect', 'getUsersTable', 'set'];

        parent::setUp();
        $request = new ServerRequest();
        $this->Trait = $this->getMockBuilder($this->traitClassName)
            ->setMethods(['dispatchEvent', 'redirect', 'set', 'getUsersTable'])
            ->getMock();

        $this->Trait->setRequest($request);
        Configure::write('Auth.AuthenticationComponent.loginAction', $this->loginPage);
    }

    /**
     * tearDown
     *
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * testVerifyHappy
     */
    public function testVerifyHappy()
    {
        Configure::write('OneTimePasswordAuthenticator.login', true);
        $request = $this->getMockBuilder('Cake\Http\ServerRequest')
            ->setMethods(['is', 'getData', 'allow', 'getSession'])
            ->getMock();
        $this->Trait->setRequest($request);
        $this->Trait->getRequest()->expects($this->once())
            ->method('is')
            ->with('post')
            ->will($this->returnValue(false));
        $this->Trait->expects($this->never())
            ->method('redirect');

        $this->_mockSession([
            'temporarySession' => [
                'id' => 1,
                'secret_verified' => 1,
            ],
        ]);

        $this->Trait->verify();
    }

    /**
     * testVerifyHappy
     */
    public function testVerifyNotEnabled()
    {
        $this->_mockFlash();
        Configure::write('OneTimePasswordAuthenticator.login', false);
        $this->Trait->setRequest($this->Trait->getRequest()->withQueryParams(['redirect' => 'dashboard/list']));
        $this->Trait->Flash->expects($this->once())
            ->method('error')
            ->with('Please enable Google Authenticator first.');
        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with($this->loginPage + ['?' => ['redirect' => 'dashboard/list']]);

        $this->Trait->verify();
    }

    /**
     * testVerifyHappy
     */
    public function testVerifyGetShowQR()
    {
        Configure::write('OneTimePasswordAuthenticator.login', true);
        $this->Trait->OneTimePasswordAuthenticator = $this->getMockBuilder(OneTimePasswordAuthenticatorComponent::class)
            ->disableOriginalConstructor()
            ->setMethods(['createSecret', 'getQRCodeImageAsDataUri'])
            ->getMock();

        $request = $this->getMockBuilder(ServerRequest::class)
            ->setMethods(['is', 'getData', 'allow', 'getSession'])
            ->getMock();
        $this->Trait->setRequest($request);

        $this->_mockSession([
            'temporarySession' => [
                'id' => '00000000-0000-0000-0000-000000000001',
                'email' => 'email@example.com',
                'secret_verified' => 0,
            ],
        ]);
        $this->Trait->expects($this->any())
            ->method('getUsersTable')
            ->will($this->returnValue(TableRegistry::getTableLocator()->get('CakeDC/Users.Users')));

        $this->Trait->getRequest()->expects($this->once())
            ->method('is')
            ->with('post')
            ->will($this->returnValue(false));
        $this->Trait->OneTimePasswordAuthenticator->expects($this->at(0))
            ->method('createSecret')
            ->will($this->returnValue('newSecret'));
        $this->Trait->OneTimePasswordAuthenticator->expects($this->at(1))
            ->method('getQRCodeImageAsDataUri')
            ->with('email@example.com', 'newSecret')
            ->will($this->returnValue('newDataUriGenerated'));
        $this->Trait->expects($this->once())
            ->method('set')
            ->with(['secretDataUri' => 'newDataUriGenerated']);

        $this->Trait->verify();
        $user = $this->Trait->getUsersTable()->findById('00000000-0000-0000-0000-000000000001')->firstOrFail();
        $this->assertEquals('newSecret', $user->secret);
    }

    /**
     * Tests that a GET request causes a a new secret to be generated in case it's
     * not already present in the session.
     */
    public function testVerifyGetGeneratesNewSecret()
    {
        Configure::write('OneTimePasswordAuthenticator.login', true);

        $this->Trait->OneTimePasswordAuthenticator = $this
            ->getMockBuilder(OneTimePasswordAuthenticatorComponent::class)
            ->disableOriginalConstructor()
            ->setMethods(['createSecret', 'getQRCodeImageAsDataUri'])
            ->getMock();

        $request = $this->getMockBuilder(ServerRequest::class)
            ->setMethods(['is', 'getData', 'allow', 'getSession'])
            ->getMock();
        $this->Trait->setRequest($request);
        $this->Trait
            ->getRequest()
            ->expects($this->once())
            ->method('is')
            ->with('post')
            ->will($this->returnValue(false));

        $this->Trait->OneTimePasswordAuthenticator
            ->expects($this->at(0))
            ->method('createSecret')
            ->will($this->returnValue('newSecret'));
        $this->Trait->OneTimePasswordAuthenticator
            ->expects($this->at(1))
            ->method('getQRCodeImageAsDataUri')
            ->with('email@example.com', 'newSecret')
            ->will($this->returnValue('newDataUriGenerated'));

        $session = $this->_mockSession([
            'temporarySession' => [
                'id' => '00000000-0000-0000-0000-000000000001',
                'email' => 'email@example.com',
                'secret_verified' => false,
            ],
        ]);
        $this->Trait->expects($this->any())
            ->method('getUsersTable')
            ->will($this->returnValue(TableRegistry::getTableLocator()->get('CakeDC/Users.Users')));
        $this->Trait->verify();

        $this->assertEquals(
            [
                'temporarySession' => [
                    'id' => '00000000-0000-0000-0000-000000000001',
                    'email' => 'email@example.com',
                    'secret_verified' => false,
                    'secret' => 'newSecret',
                ],
            ],
            $session->read()
        );
    }

    /**
     * Tests that a GET request does not cause a new secret to be generated in case
     * it's already present in the session.
     */
    public function testVerifyGetDoesNotGenerateNewSecret()
    {
        Configure::write('OneTimePasswordAuthenticator.login', true);

        $this->Trait->OneTimePasswordAuthenticator = $this
            ->getMockBuilder(OneTimePasswordAuthenticatorComponent::class)
            ->disableOriginalConstructor()
            ->setMethods(['createSecret', 'getQRCodeImageAsDataUri'])
            ->getMock();

        $request = $this->getMockBuilder(ServerRequest::class)
            ->setMethods(['is', 'getData', 'allow', 'getSession'])
            ->getMock();
        $this->Trait->setRequest($request);
        $this->Trait
            ->getRequest()
            ->expects($this->once())
            ->method('is')
            ->with('post')
            ->will($this->returnValue(false));

        $this->Trait->OneTimePasswordAuthenticator
            ->expects($this->never())
            ->method('createSecret');
        $this->Trait->OneTimePasswordAuthenticator
            ->expects($this->at(0))
            ->method('getQRCodeImageAsDataUri')
            ->with('email@example.com', 'alreadyPresentSecret')
            ->will($this->returnValue('newDataUriGenerated'));

        $session = $this->_mockSession([
            'temporarySession' => [
                'id' => '00000000-0000-0000-0000-000000000001',
                'email' => 'email@example.com',
                'secret_verified' => false,
                'secret' => 'alreadyPresentSecret',
            ],
        ]);
        $this->Trait->expects($this->any())
            ->method('getUsersTable')
            ->will($this->returnValue(TableRegistry::getTableLocator()->get('CakeDC/Users.Users')));
        $this->Trait->verify();

        $this->assertEquals(
            [
                'temporarySession' => [
                    'id' => '00000000-0000-0000-0000-000000000001',
                    'email' => 'email@example.com',
                    'secret_verified' => false,
                    'secret' => 'alreadyPresentSecret',
                ],
            ],
            $session->read()
        );
    }
}

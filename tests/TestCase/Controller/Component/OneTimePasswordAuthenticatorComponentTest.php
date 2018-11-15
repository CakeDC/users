<?php
/**
 * Copyright 2010 - 2017, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2017, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Test\TestCase\Controller\Component;

use CakeDC\Users\Controller\Component\OneTimePasswordAuthenticatorComponent;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use Cake\Utility\Security;

class OneTimePasswordAuthenticatorComponentTest extends TestCase
{
    public $fixtures = [
        'plugin.CakeDC/Users.users'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->backupUsersConfig = Configure::read('Users');

        Router::reload();
        Router::connect('/route/*', [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'requestResetPassword'
        ]);
        Router::connect('/notAllowed/*', [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'edit'
        ]);

        Security::setSalt('YJfIxfs2guVoUubWDYhG93b0qyJfIxfs2guwvniR2G0FgaC9mi');
        Configure::write('App.namespace', 'Users');
        Configure::write('Users.OneTimePasswordAuthenticator.login', true);

        $this->request = $this->getMockBuilder('Cake\Http\ServerRequest')
                ->setMethods(['is', 'method'])
                ->getMock();
        $this->request->expects($this->any())->method('is')->will($this->returnValue(true));
        $this->response = $this->getMockBuilder('Cake\Http\Response')
                ->setMethods(['stop'])
                ->getMock();
        $this->Controller = new Controller($this->request, $this->response);
        $this->Registry = $this->Controller->components();
        $this->Controller->OneTimePasswordAuthenticator = new OneTimePasswordAuthenticatorComponent($this->Registry);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        $_SESSION = [];
        unset($this->Controller, $this->OneTimePasswordAuthenticator);
        Configure::write('Users', $this->backupUsersConfig);
        Configure::write('Users.OneTimePasswordAuthenticator.login', false);
    }

    /**
     * Test initialize
     *
     */
    public function testInitialize()
    {
        $this->Controller->OneTimePasswordAuthenticator = new OneTimePasswordAuthenticatorComponent($this->Registry);
        $this->assertInstanceOf('CakeDC\Users\Controller\Component\OneTimePasswordAuthenticatorComponent', $this->Controller->OneTimePasswordAuthenticator);
    }

    /**
     * test base64 qr-code returned from component
     * @return void
     */
    public function testgetQRCodeImageAsDataUri()
    {
        $this->Controller->OneTimePasswordAuthenticator->initialize([]);
        $result = $this->Controller->OneTimePasswordAuthenticator->getQRCodeImageAsDataUri('test@localhost.com', '123123');

        $this->assertContains('data:image/png;base64', $result);
    }

    /**
     * Making sure we return secret
     * @return void
     */
    public function testCreateSecret()
    {
        $this->Controller->OneTimePasswordAuthenticator->initialize([]);
        $result = $this->Controller->OneTimePasswordAuthenticator->createSecret();
        $this->assertNotEmpty($result);
    }

    /**
     * Testing code verification in the component
     * @return void
     */
    public function testVerifyCode()
    {
        $this->Controller->OneTimePasswordAuthenticator->initialize([]);
        $secret = $this->Controller->OneTimePasswordAuthenticator->createSecret();
        $verificationCode = $this->Controller->OneTimePasswordAuthenticator->tfa->getCode($secret);

        $verified = $this->Controller->OneTimePasswordAuthenticator->verifyCode($secret, $verificationCode);
        $this->assertTrue($verified);
    }
}

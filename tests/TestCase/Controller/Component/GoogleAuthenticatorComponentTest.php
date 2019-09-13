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

use CakeDC\Users\Controller\Component\GoogleAuthenticatorComponent;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use Cake\Utility\Security;

class GoogleAuthenticatorComponentTest extends TestCase
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
        Configure::write('Error.errorLevel', E_ALL & ~E_DEPRECATED);
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
        Configure::write('Users.GoogleAuthenticator.login', true);

        $this->request = $this->getMockBuilder('Cake\Http\ServerRequest')
                ->setMethods(['is', 'method'])
                ->getMock();
        $this->request->expects($this->any())->method('is')->will($this->returnValue(true));
        $this->response = $this->getMockBuilder('Cake\Http\Response')
                ->setMethods(['stop'])
                ->getMock();
        $this->Controller = new Controller($this->request, $this->response);
        $this->Registry = $this->Controller->components();
        $this->Controller->GoogleAuthenticator = new GoogleAuthenticatorComponent($this->Registry);
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
        unset($this->Controller, $this->GoogleAuthenticator);
        Configure::write('Users', $this->backupUsersConfig);
        Configure::write('Users.GoogleAuthenticator.login', false);
    }

    /**
     * Test initialize
     *
     */
    public function testInitialize()
    {
        $this->Controller->GoogleAuthenticator = new GoogleAuthenticatorComponent($this->Registry);
        $this->assertInstanceOf('CakeDC\Users\Controller\Component\GoogleAuthenticatorComponent', $this->Controller->GoogleAuthenticator);
    }

    /**
     * test base64 qr-code returned from component
     * @return void
     */
    public function testgetQRCodeImageAsDataUri()
    {
        $this->Controller->GoogleAuthenticator->initialize([]);
        $result = $this->Controller->GoogleAuthenticator->getQRCodeImageAsDataUri('test@localhost.com', '123123');

        $this->assertContains('data:image/png;base64', $result);
    }

    /**
     * Making sure we return secret
     * @return void
     */
    public function testCreateSecret()
    {
        $this->Controller->GoogleAuthenticator->initialize([]);
        $result = $this->Controller->GoogleAuthenticator->createSecret();
        $this->assertNotEmpty($result);
    }

    /**
     * Testing code verification in the component
     * @return void
     */
    public function testVerifyCode()
    {
        $this->Controller->GoogleAuthenticator->initialize([]);
        $secret = $this->Controller->GoogleAuthenticator->createSecret();
        $verificationCode = $this->Controller->GoogleAuthenticator->tfa->getCode($secret);

        $verified = $this->Controller->GoogleAuthenticator->verifyCode($secret, $verificationCode);
        $this->assertTrue($verified);
    }

    /**
     * Test getChecker method
     *
     * @return void
     */
    public function testGetChecker()
    {
        $result = $this->Controller->GoogleAuthenticator->getChecker();
        $this->assertInstanceOf(DefaultTwoFactorAuthenticationChecker::class, $result);
    }

    /**
     * Test getChecker method
     *
     * @return void
     */
    public function testGetCheckerInvalidInterface()
    {
        Configure::write('GoogleAuthenticator.checker', 'stdClass');
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid config for 'GoogleAuthenticator.checker', 'stdClass' does not implement 'CakeDC\Users\Auth\TwoFactorAuthenticationCheckerInterface'");
        $this->Controller->GoogleAuthenticator->getChecker();
    }
}

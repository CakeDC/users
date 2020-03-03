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

namespace CakeDC\Users\Test\TestCase\Controller;

use Cake\Core\Configure;
use Cake\Http\ServerRequest;
use Cake\Mailer\Email;
use Cake\Mailer\TransportFactory;
use Cake\TestSuite\TestCase;

class SocialAccountsControllerTest extends TestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.CakeDC/Users.SocialAccounts',
        'plugin.CakeDC/Users.Users',
    ];

    /**
     * setUp
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->configOpauth = Configure::read('Opauth');
        $this->configRememberMe = Configure::read('Users.RememberMe.active');
        Configure::write('Opauth', null);
        Configure::write('Users.RememberMe.active', false);

        TransportFactory::setConfig('test', ['className' => 'Debug']);
        $this->configEmail = Email::getConfig('default');
        Email::drop('default');
        Email::setConfig('default', [
            'transport' => 'test',
            'from' => 'cakedc@example.com',
        ]);

        $request = new ServerRequest(['url' => '/users/users/index']);
        $request = $request->withParam('plugin', 'CakeDC/Users');

        $this->Controller = $this->getMockBuilder('CakeDC\Users\Controller\SocialAccountsController')
                ->onlyMethods(['redirect', 'render'])
                ->setConstructorArgs([$request, null, 'SocialAccounts'])
                ->getMock();
    }

    /**
     * tearDown
     *
     * @return void
     */
    public function tearDown(): void
    {
        Email::drop('default');
        TransportFactory::drop('test');
        //Email::setConfig('default', $this->configEmail);

        Configure::write('Opauth', $this->configOpauth);
        Configure::write('Users.RememberMe.active', $this->configRememberMe);

        parent::tearDown();
    }

    /**
     * test
     *
     * @return void
     */
    public function testValidateAccountHappy()
    {
        $this->Controller->expects($this->once())
                ->method('redirect')
                ->with(['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'login', 'prefix' => false]);
        $this->Controller->validateAccount('Facebook', 'reference-1-1234', 'token-1234');
        $this->assertEquals('Account validated successfully', $this->Controller->getRequest()->getSession()->read('Flash.flash.0.message'));
    }

    /**
     * test
     *
     * @return void
     */
    public function testValidateAccountInvalidToken()
    {
        $this->Controller->expects($this->once())
                ->method('redirect')
                ->with(['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'login', 'prefix' => false]);
        $this->Controller->validateAccount('Facebook', 'reference-1-1234', 'token-not-found');
        $this->assertEquals('Invalid token and/or social account', $this->Controller->getRequest()->getSession()->read('Flash.flash.0.message'));
    }

    /**
     * test
     *
     * @return void
     */
    public function testValidateAccountAlreadyActive()
    {
        $this->Controller->expects($this->once())
                ->method('redirect')
                ->with(['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'login', 'prefix' => false]);
        $this->Controller->validateAccount('Twitter', 'reference-1-1234', 'token-1234');
        $this->assertEquals('Social Account already active', $this->Controller->getRequest()->getSession()->read('Flash.flash.0.message'));
    }

    /**
     * test
     *
     * @return void
     */
    public function testResendValidationHappy()
    {
        $behaviorMock = $this->getMockBuilder('CakeDC\Users\Model\Behavior\SocialAccountBehavior')
                ->onlyMethods(['sendSocialValidationEmail'])
                ->setConstructorArgs([$this->Controller->SocialAccounts])
                ->getMock();
        $this->Controller->SocialAccounts->behaviors()->set('SocialAccount', $behaviorMock);
        $behaviorMock->expects($this->once())
                ->method('sendSocialValidationEmail')
                ->will($this->returnValue(true));
        $this->Controller->expects($this->once())
                ->method('redirect')
                ->with(['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'login', 'prefix' => false]);

        $this->Controller->resendValidation('Facebook', 'reference-1-1234');
        $this->assertEquals('Email sent successfully', $this->Controller->getRequest()->getSession()->read('Flash.flash.0.message'));
    }

    /**
     * test
     *
     * @return void
     */
    public function testResendValidationEmailError()
    {
        $behaviorMock = $this->getMockBuilder('CakeDC\Users\Model\Behavior\SocialAccountBehavior')
                ->onlyMethods(['sendSocialValidationEmail'])
                ->setConstructorArgs([$this->Controller->SocialAccounts])
                ->getMock();
        $this->Controller->SocialAccounts->behaviors()->set('SocialAccount', $behaviorMock);
        $behaviorMock->expects($this->once())
                ->method('sendSocialValidationEmail')
                ->will($this->returnValue(false));
        $this->Controller->expects($this->once())
                ->method('redirect')
                ->with(['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'login', 'prefix' => false]);

        $this->Controller->resendValidation('Facebook', 'reference-1-1234');
        $this->assertEquals('Email could not be sent', $this->Controller->getRequest()->getSession()->read('Flash.flash.0.message'));
    }

    /**
     * test
     *
     * @return void
     */
    public function testResendValidationInvalid()
    {
        $this->Controller->expects($this->once())
                ->method('redirect')
                ->with(['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'login', 'prefix' => false]);
        $this->Controller->resendValidation('Facebook', 'reference-invalid');
        $this->assertEquals('Invalid account', $this->Controller->getRequest()->getSession()->read('Flash.flash.0.message'));
    }

    /**
     * test
     *
     * @return void
     */
    public function testResendValidationAlreadyActive()
    {
        $this->Controller->expects($this->once())
                ->method('redirect')
                ->with(['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'login', 'prefix' => false]);
        $this->Controller->validateAccount('Twitter', 'reference-1-1234', 'token-1234');
        $this->assertEquals('Social Account already active', $this->Controller->getRequest()->getSession()->read('Flash.flash.0.message'));
    }
}

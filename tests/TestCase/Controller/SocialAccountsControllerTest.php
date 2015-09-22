<?php
/**
 * Copyright 2010 - 2015, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2015, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Test\TestCase\Controller;

use CakeDC\Users\Controller\SocialAccountsController;
use CakeDC\Users\Model\Behavior\SocialAccountBehavior;
use CakeDC\Users\Model\Table\SocialAccountsTable;
use Cake\Core\Configure;
use Cake\Event\EventManager;
use Cake\Mailer\Email;
use Cake\Network\Request;
use Cake\TestSuite\TestCase;

class SocialAccountsControllerTest extends TestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.CakeDC/Users.social_accounts',
        'plugin.CakeDC/Users.users'
    ];

    /**
     * setUp
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->configOpauth = Configure::read('Opauth');
        $this->configRememberMe = Configure::read('Users.RememberMe.active');
        Configure::write('Opauth', null);
        Configure::write('Users.RememberMe.active', false);

        Email::configTransport('test', [
            'className' => 'Debug'
        ]);
        $this->configEmail = Email::config('default');
        Email::config('default', [
            'transport' => 'test',
            'from' => 'cakedc@example.com'
        ]);

        $request = new Request('/users/users/index');
        $request->params['plugin'] = 'CakeDC/Users';

        $this->Controller = $this->getMockBuilder('CakeDC\Users\Controller\SocialAccountsController')
                ->setMethods(['redirect', 'render'])
                ->setConstructorArgs([$request, null, 'SocialAccounts'])
                ->getMock();
        $this->Controller->SocialAccounts = $this->getMockForModel('CakeDC\Users.SocialAccounts', ['sendSocialValidationEmail'], [
            'className' => 'CakeDC\Users\Model\Table\SocialAccountsTable'
        ]);
    }

    /**
     * tearDown
     *
     * @return void
     */
    public function tearDown()
    {
        Email::drop('default');
        Email::dropTransport('test');
        Email::config('default', $this->configEmail);

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
                ->with(['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'login']);
        $this->Controller->validateAccount('Facebook', 'reference-1-1234', 'token-1234');
        $this->assertEquals('Account validated successfully', $this->Controller->request->session()->read('Flash.flash.0.message'));
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
                ->with(['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'login']);
        $this->Controller->validateAccount('Facebook', 'reference-1-1234', 'token-not-found');
        $this->assertEquals('Invalid token and/or social account', $this->Controller->request->session()->read('Flash.flash.0.message'));
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
                ->with(['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'login']);
        $this->Controller->validateAccount('Twitter', 'reference-1-1234', 'token-1234');
        $this->assertEquals('SocialAccount already active', $this->Controller->request->session()->read('Flash.flash.0.message'));
    }

    /**
     * test
     *
     * @return void
     */
    public function testResendValidationHappy()
    {
        $behaviorMock = $this->getMockBuilder('CakeDC\Users\Model\Behavior\SocialAccountBehavior')
                ->setMethods(['sendSocialValidationEmail'])
                ->setConstructorArgs([$this->Controller->SocialAccounts])
                ->getMock();
        $this->Controller->SocialAccounts->behaviors()->set('SocialAccount', $behaviorMock);
        $behaviorMock->expects($this->once())
                ->method('sendSocialValidationEmail')
                ->will($this->returnValue(true));
        $this->Controller->expects($this->once())
                ->method('redirect')
                ->with(['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'login']);

        $this->Controller->resendValidation('Facebook', 'reference-1-1234');
        $this->assertEquals('Email sent successfully', $this->Controller->request->session()->read('Flash.flash.0.message'));
    }

    /**
     * test
     *
     * @return void
     */
    public function testResendValidationEmailError()
    {
        $behaviorMock = $this->getMockBuilder('CakeDC\Users\Model\Behavior\SocialAccountBehavior')
                ->setMethods(['sendSocialValidationEmail'])
                ->setConstructorArgs([$this->Controller->SocialAccounts])
                ->getMock();
        $this->Controller->SocialAccounts->behaviors()->set('SocialAccount', $behaviorMock);
        $behaviorMock->expects($this->once())
                ->method('sendSocialValidationEmail')
                ->will($this->returnValue(false));
        $this->Controller->expects($this->once())
                ->method('redirect')
                ->with(['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'login']);

        $this->Controller->resendValidation('Facebook', 'reference-1-1234');
        $this->assertEquals('Email could not be sent', $this->Controller->request->session()->read('Flash.flash.0.message'));
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
                ->with(['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'login']);
        $this->Controller->resendValidation('Facebook', 'reference-invalid');
        $this->assertEquals('Invalid account', $this->Controller->request->session()->read('Flash.flash.0.message'));
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
                ->with(['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'login']);
        $this->Controller->validateAccount('Twitter', 'reference-1-1234', 'token-1234');
        $this->assertEquals('SocialAccount already active', $this->Controller->request->session()->read('Flash.flash.0.message'));
    }
}

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
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\ORM\TableRegistry;
use CakeDC\Auth\Controller\Component\OneTimePasswordAuthenticatorComponent;
use CakeDC\Auth\Authentication\AuthenticationService;
use CakeDC\Auth\Authentication\Code2fAuthenticationCheckerInterface;

class Code2fTraitTest extends BaseTraitTest
{

    public $fixtures = [
        'plugin.CakeDC/Users.OtpCodes',
        'plugin.CakeDC/Users.Users',
    ];

    /**
     * setup
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->traitClassName = 'CakeDC\Users\Controller\UsersController';
        $this->traitMockMethods = ['dispatchEvent', 'isStopped', 'redirect', 'getUsersTable', 'set', 'redirectWithQuery', 'viewBuilder'];
        //$this->mockDefaultEmail = true;
        parent::setUp();
    }

    public function testCode2fWithValidAndRegistration()
    {
        $this->prepareRequest('00000000-0000-0000-0000-000000000001');

        Configure::write('Code2f.enabled', true);
        $this->Trait->expects($this->once())
            ->method('redirectWithQuery')
            ->with($this->equalTo([
                'action' => 'code2fAuthenticate',
            ]));

        $this->Trait->code2f();
    }

    public function testCode2fWithoutValid()
    {
        $this->prepareRequest('00000000-0000-0000-0000-000000000001');

        $this->Trait->expects($this->once())
            ->method('redirectWithQuery')
            ->with($this->equalTo([
                'action' => 'login',
            ]));

        $this->Trait->code2f();
    }

    public function testCode2fWithoutRegistration()
    {
        $this->prepareRequest('00000000-0000-0000-0000-000000000002');

        Configure::write('Code2f.enabled', true);
        Configure::write('Code2f.type', Code2fAuthenticationCheckerInterface::CODE2F_TYPE_PHONE);

        $this->Trait->expects($this->once())
            ->method('redirectWithQuery')
            ->with($this->equalTo([
                'action' => 'code2fRegister',
            ]));

        $this->Trait->code2f();
    }

    public function testCode2fRegisterWithoutValid()
    {
        $this->prepareRequest('00000000-0000-0000-0000-000000000001');

        $this->Trait->expects($this->once())
            ->method('redirectWithQuery')
            ->with($this->equalTo([
                'action' => 'login',
            ]));

        $this->Trait->code2fRegister();
    }

    public function testCode2fRegisterWithregistration()
    {
        $this->prepareRequest('00000000-0000-0000-0000-000000000002');

        Configure::write('Code2f.enabled', true);
        $this->Trait->expects($this->once())
            ->method('redirectWithQuery')
            ->with($this->equalTo([
                'action' => 'code2fAuthenticate',
            ]));

        $this->Trait->code2fRegister();
    }

    public function testCode2fRegisterWithoutregistration()
    {
        $this->prepareRequest('00000000-0000-0000-0000-000000000002');

        Configure::write('Code2f.enabled', true);
        Configure::write('Code2f.type', Code2fAuthenticationCheckerInterface::CODE2F_TYPE_PHONE);
        
        $this->Trait->expects($this->once())
            ->method('viewBuilder')
            ->will($this->returnValue(new \Cake\View\ViewBuilder()));

        $this->Trait->code2fRegister();
    }

    public function testCode2fRegisterPostEmail()
    {
        $this->prepareRequest('00000000-0000-0000-0000-000000000002');

        $email_code = Code2fAuthenticationCheckerInterface::CODE2F_TYPE_EMAIL;
        Configure::write('Code2f.type', $email_code);
        Configure::write('Code2f.enabled', true);
        
        $this->Trait->getRequest()
            ->expects($this->once())
            ->method('is')
            ->with(['post', 'put'])
            ->will($this->returnValue(true));

        $this->Trait->getRequest()
            ->expects($this->once())
            ->method('getData')
            ->with($email_code)
            ->will($this->returnValue($this->user->email));
        
        $this->Trait->expects($this->once())
            ->method('redirectWithQuery')
            ->with($this->equalTo([
                'action' => 'code2fAuthenticate',
            ]));

        $user = $this->Trait->getRequest()->getSession()->read(AuthenticationService::CODE2F_SESSION_KEY);
        $this->assertEquals($this->user, $user);

        $this->Trait->code2fRegister();
    }

    public function testCode2fRegisterPostPhone()
    {
        $this->prepareRequest('00000000-0000-0000-0000-000000000002');
        $this->_mockFlash();

        $phone_code = Code2fAuthenticationCheckerInterface::CODE2F_TYPE_PHONE;
        Configure::write('Code2f.type', $phone_code);
        Configure::write('Code2f.enabled', true);
        //Configure::write('Code2f.config', 'sms');
        
        $this->Trait->getRequest()
            ->expects($this->once())
            ->method('is')
            ->with(['post', 'put'])
            ->will($this->returnValue(true));

        $this->Trait->getRequest()
            ->expects($this->once())
            ->method('getData')
            ->with($phone_code)
            ->will($this->returnValue('WRONG PHONE'));

        $this->Trait->Flash
            ->expects($this->once())
            ->method('error');

        $this->Trait->code2fRegister();
    }

    public function testCode2fAuthenticateWithoutValid()
    {
        $this->prepareRequest('00000000-0000-0000-0000-000000000001');

        $this->Trait->expects($this->once())
            ->method('redirectWithQuery')
            ->with($this->equalTo(Configure::read('Auth.AuthenticationComponent.loginAction')));

        $this->Trait->code2fAuthenticate();

    }

    public function testCode2fAuthenticateWithoutregistration()
    {
        $this->prepareRequest('00000000-0000-0000-0000-000000000001');
        Configure::write('Code2f.enabled', true);
        Configure::write('Code2f.type', Code2fAuthenticationCheckerInterface::CODE2F_TYPE_PHONE);

        $this->Trait->expects($this->once())
            ->method('redirectWithQuery')
            ->with($this->equalTo([
                'action' => 'code2fRegister',
            ]));

        $this->Trait->code2fAuthenticate();
    }

    public function testCode2fAuthenticateNoPost()
    {
        $this->prepareRequest('00000000-0000-0000-0000-000000000002');
        Configure::write('Code2f.enabled', true);

        $this->Trait->expects($this->once())
            ->method('viewBuilder')
            ->will($this->returnValue(new \Cake\View\ViewBuilder()));
    
        $this->Trait->code2fAuthenticate();
    }

    public function testCode2fAuthenticatePostWithoutResend()
    {
        $this->prepareRequest('00000000-0000-0000-0000-000000000002');
        Configure::write('Code2f.enabled', true);

        $this->Trait->getRequest()
            ->expects($this->any())
            ->method('is')
            ->with(['post', 'put'])
            ->will($this->returnValue(true));


        $this->Trait->getRequest()
            ->expects($this->once())
            ->method('getQuery')
            ->with('resend')
            ->will($this->returnValue(true));
            
        $this->Trait->expects($this->once())
            ->method('redirectWithQuery')
            ->with($this->equalTo([
                'action' => 'code2fAuthenticate',
            ]));

        $this->Trait->code2fAuthenticate();
    }

    protected function prepareRequest($user_id = '00000000-0000-0000-0000-000000000001', $session = [])
    {
        $request = $this->getMockBuilder('Cake\Http\ServerRequest')
            ->onlyMethods(['is', 'getData', 'getQuery','getSession'])
            ->getMock();
        $this->Trait->setRequest($request);
        
        $this->user = $this->Trait->getUsersTable()->findById($user_id)->firstOrFail();
        $this->_mockSession(array_merge(
            [AuthenticationService::CODE2F_SESSION_KEY => $this->user],
            $session
        ));
    }

}

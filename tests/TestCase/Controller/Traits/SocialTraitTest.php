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

namespace CakeDC\Users\Test\TestCase\Controller\Traits;

use Cake\Core\Configure;
use Cake\TestSuite\TestCase;

class SocialTraitTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->controller = $this->getMockBuilder('Cake\Controller\Controller')
            ->setMethods(['header', 'redirect', 'render', '_stop'])
            ->getMock();

        $this->controller->Trait = $this->getMockForTrait(
            'CakeDC\Users\Controller\Traits\SocialTrait',
            [],
            '',
            true,
            true,
            true,
            ['_getOpauthInstance', 'redirect', '_generateOpauthCompleteUrl', '_afterIdentifyUser', '_validateRegisterPost']
        );
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * Test socialEmail
     *
     */
    public function testSocialEmail()
    {
        $session = $this->getMockBuilder('Cake\Network\Session')
                ->setMethods(['check', 'delete'])
                ->getMock();
        $session->expects($this->at(0))
            ->method('check')
            ->with('Users.social')
            ->will($this->returnValue('social_key'));

        $session->expects($this->at(1))
            ->method('delete')
            ->with('Flash.auth');

        $this->controller->Trait->request = $this->getMockBuilder('Cake\Network\Request')
                ->setMethods(['session'])
                ->getMock();
        $this->controller->Trait->request->expects($this->any())
            ->method('session')
            ->will($this->returnValue($session));

        $this->controller->Trait->socialEmail();
    }

    /**
     * Test socialEmail
     *
     * @expectedException \Cake\Network\Exception\NotFoundException
     */
    public function testSocialEmailInvalid()
    {
        $session = $this->getMockBuilder('Cake\Network\Session')
                ->setMethods(['check'])
                ->getMock();
        $session->expects($this->once())
            ->method('check')
            ->with('Users.social')
            ->will($this->returnValue(null));

        $this->controller->Trait->request = $this->getMockBuilder('Cake\Network\Request')
                ->setMethods(['session'])
                ->getMock();
        $this->controller->Trait->request->expects($this->once())
            ->method('session')
            ->will($this->returnValue($session));

        $this->controller->Trait->socialEmail();
    }

    public function testSocialEmailPostValidateFalse()
    {
        $session = $this->getMockBuilder('Cake\Network\Session')
                ->setMethods(['check', 'delete'])
                ->getMock();
        $session->expects($this->any())
            ->method('check')
            ->with('Users.social')
            ->will($this->returnValue(true));

        $session->expects($this->once())
            ->method('delete')
            ->with('Flash.auth');

        $this->controller->Trait->request = $this->getMockBuilder('Cake\Network\Request')
                ->setMethods(['session', 'is'])
                ->getMock();
        $this->controller->Trait->request->expects($this->any())
            ->method('session')
            ->will($this->returnValue($session));

        $this->controller->Trait->request->expects($this->once())
            ->method('is')
            ->with('post')
            ->will($this->returnValue(true));

        $this->controller->Trait->expects($this->once())
            ->method('_validateRegisterPost')
            ->will($this->returnValue(false));

        $this->controller->Trait->Flash = $this->getMockBuilder('Cake\Controller\Component\FlashComponent')
            ->setMethods(['error'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->controller->Trait->Flash->expects($this->once())
            ->method('error')
            ->with('The reCaptcha could not be validated');

        $this->controller->Trait->socialEmail();
    }

    public function testSocialEmailPostValidateTrue()
    {
        $session = $this->getMockBuilder('Cake\Network\Session')
                ->setMethods(['check', 'delete'])
                ->getMock();
        $session->expects($this->any())
            ->method('check')
            ->with('Users.social')
            ->will($this->returnValue(true));

        $session->expects($this->once())
            ->method('delete')
            ->with('Flash.auth');

        $this->controller->Trait->request = $this->getMockBuilder('Cake\Network\Request')
                ->setMethods(['session', 'is'])
                ->getMock();
        $this->controller->Trait->request->expects($this->any())
            ->method('session')
            ->will($this->returnValue($session));

        $this->controller->Trait->request->expects($this->once())
            ->method('is')
            ->with('post')
            ->will($this->returnValue(true));

        $this->controller->Trait->expects($this->once())
            ->method('_validateRegisterPost')
            ->will($this->returnValue(true));

        $this->controller->Trait->Auth = $this->getMockBuilder('Cake\Controller\Component\AuthComponent')
            ->setMethods(['identify'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->controller->Trait->Auth->expects($this->once())
            ->method('identify');

        $this->controller->Trait->expects($this->once())
            ->method('_afterIdentifyUser');

        $this->controller->Trait->socialEmail();
    }
}

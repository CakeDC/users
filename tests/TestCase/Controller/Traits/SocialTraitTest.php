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

namespace CakeDC\Users\Test\TestCase\Controller\Traits;

use Cake\Core\Configure;
use Cake\TestSuite\TestCase;

class SocialTraitTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->controller = $this->getMock(
            'Cake\Controller\Controller',
            ['header', 'redirect', 'render', '_stop']
        );
        $this->controller->Trait = $this->getMockForTrait(
            'CakeDC\Users\Controller\Traits\SocialTrait',
            [],
            '',
            true,
            true,
            true,
            ['_getOpauthInstance', 'redirect', '_generateOpauthCompleteUrl']
        );
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * Test opauthInit with no callback
     *
     */
    public function testOpauthInitTest()
    {
        $Opauth = $this->getMock('Opauth\Opauth\Opauth', ['run'], [], '', false);
        $Opauth->expects($this->once())
            ->method('run')
            ->will($this->returnValue('response'));
        $this->controller->Trait->expects($this->once())
            ->method('_getOpauthInstance')
            ->will($this->returnValue($Opauth));
        $this->controller->Trait->opauthInit();
    }

    /**
     * Test opauthInit with callback
     *
     */
    public function testOpauthInitTestCallback()
    {
        $Opauth = $this->getMock('Opauth\Opauth\Opauth', ['run'], [], '', false);
        $Opauth->expects($this->once())
            ->method('run')
            ->will($this->returnValue('response'));
        $session = $this->getMock('Cake\Network\Session', ['write', 'check']);
        $session->expects($this->once())
            ->method('write')
            ->with(Configure::read('Users.Key.Session.social'), 'response');
        $this->controller->Trait->request = $this->getMock('Cake\Network\Request', ['session']);
        $this->controller->Trait->request->expects($this->once())
            ->method('session')
            ->will($this->returnValue($session));
        $this->controller->Trait->expects($this->once())
            ->method('_getOpauthInstance')
            ->will($this->returnValue($Opauth));
        $this->controller->Trait->expects($this->once())
            ->method('_generateOpauthCompleteUrl')
            ->will($this->returnValue('url'));
        $this->controller->Trait->expects($this->once())
            ->method('redirect')
            ->with('url');
        $this->controller->Trait->opauthInit('callback');
    }

    /**
     * Test socialEmail
     *
     */
    public function testSocialEmail()
    {
        $session = $this->getMock('Cake\Network\Session', ['check']);
        $session->expects($this->once())
            ->method('check')
            ->with('Users.social')
            ->will($this->returnValue('social_key'));

        $this->controller->Trait->request = $this->getMock('Cake\Network\Request', ['session']);
        $this->controller->Trait->request->expects($this->once())
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
        $session = $this->getMock('Cake\Network\Session', ['check']);
        $session->expects($this->once())
            ->method('check')
            ->with('Users.social')
            ->will($this->returnValue(null));

        $this->controller->Trait->request = $this->getMock('Cake\Network\Request', ['session']);
        $this->controller->Trait->request->expects($this->once())
            ->method('session')
            ->will($this->returnValue($session));

        $this->controller->Trait->socialEmail();
    }
}

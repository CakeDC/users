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

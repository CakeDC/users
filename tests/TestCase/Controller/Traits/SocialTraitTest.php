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

use Cake\Http\Response;
use Cake\Http\ServerRequest;
use CakeDC\Users\Middleware\SocialAuthMiddleware;

class SocialTraitTest extends BaseTraitTest
{
    /**
     * setup
     *
     * @return void
     */
    public function setUp()
    {
        $this->traitClassName = 'CakeDC\Users\Controller\Traits\SocialTrait';
        $this->traitMockMethods = ['dispatchEvent', 'isStopped', 'redirect', 'getUsersTable', 'set', '_afterIdentifyUser'];

        parent::setUp();
        $request = new ServerRequest();
        $this->Trait = $this->getMockBuilder('CakeDC\Users\Controller\Traits\SocialTrait')
            ->setMethods(['dispatchEvent', 'redirect', 'set', '_afterIdentifyUser'])
            ->getMockForTrait();

        $this->Trait->request = $request;
    }

    /**
     * tearDown
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * Test socialEmail get
     *
     */
    public function testSocialEmailHappyGet()
    {
        $this->Trait->request = $this->getMockBuilder('Cake\Http\ServerRequest')
            ->setMethods(['is'])
            ->getMock();
        $this->Trait->request->expects($this->any())
            ->method('is')
            ->with('post')
            ->will($this->returnValue(false));
        $this->_mockAuthentication();
        $this->Trait->Flash = $this->getMockBuilder('Cake\Controller\Component\FlashComponent')
            ->setMethods(['error'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->Trait->Flash->expects($this->never())
            ->method('error');
        $this->Trait->expects($this->never())
            ->method('_afterIdentifyUser');
        $this->Trait->expects($this->never())
            ->method('redirect');

        $this->Trait->socialEmail();
    }
    /**
     * Test socialEmail
     *
     */
    public function testSocialEmailHappy()
    {
        $this->Trait->request = $this->getMockBuilder('Cake\Http\ServerRequest')
            ->setMethods(['is'])
            ->getMock();
        $this->Trait->request->expects($this->any())
            ->method('is')
            ->with('post')
            ->will($this->returnValue(true));
        $this->_mockAuthentication([
            'id' => 1
        ]);
        $this->Trait->Flash = $this->getMockBuilder('Cake\Controller\Component\FlashComponent')
            ->setMethods(['error'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->Trait->Flash->expects($this->never())
            ->method('error');
        $user = $this->Trait->request->getAttribute('identity')->getOriginalData();
        $response = new Response();
        $response->withStringBody("testSocialEmailHappy");
        $this->Trait->expects($this->once())
            ->method('_afterIdentifyUser')
            ->with($user)
            ->will($this->returnValue($response));

        $this->assertSame($response, $this->Trait->socialEmail());
    }

    /**
     * Test socialEmail
     *
     */
    public function testSocialEmailInvalidRecaptcha()
    {
        $this->Trait->request = $this->getMockBuilder('Cake\Http\ServerRequest')
            ->setMethods(['is'])
            ->getMock();
        $this->Trait->request->expects($this->any())
            ->method('is')
            ->with('post')
            ->will($this->returnValue(true));
        $this->_mockAuthentication();
        $this->Trait->request = $this->Trait->request->withAttribute('socialAuthStatus', SocialAuthMiddleware::AUTH_ERROR_INVALID_RECAPTCHA);
        $this->Trait->Flash = $this->getMockBuilder('Cake\Controller\Component\FlashComponent')
            ->setMethods(['error'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->Trait->Flash->expects($this->once())
            ->method('error')
            ->with('The reCaptcha could not be validated');

        $this->Trait->expects($this->never())
            ->method('_afterIdentifyUser');

        $this->Trait->socialEmail();
    }
}

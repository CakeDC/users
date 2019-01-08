<?php
/**
 * Copyright 2010 - 2018, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2018, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Test\TestCase\View\Helper;

use Authentication\Identity;
use CakeDC\Auth\Rbac\Rbac;
use CakeDC\Users\Model\Entity\User;
use CakeDC\Users\View\Helper\AuthLinkHelper;
use Cake\TestSuite\TestCase;
use Cake\View\View;

/**
 * CakeDC\Users\View\Helper\AuthLinkHelper Test Case
 */
class AuthLinkHelperTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \CakeDC\Users\View\Helper\AuthLinkHelper
     */
    public $AuthLink;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $view = new View();
        $this->AuthLink = new AuthLinkHelper($view);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->AuthLink);

        parent::tearDown();
    }

    /**
     * Test link
     *
     * @return void
     */
    public function testLinkFalse()
    {
        $link = $this->AuthLink->link('title', ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'profile']);
        $this->assertSame(false, $link);
    }

    /**
     * Test link
     *
     * @return void
     */
    public function testLinkFalseWithMock()
    {
        $user = new User([
            'id' => '00000000-0000-0000-0000-000000000001',
            'password' => '12345'
        ]);
        $identity = new Identity($user);
        $this->AuthLink->getView()->setRequest($this->AuthLink->getView()->getRequest()->withAttribute('identity', $identity));
        $rbac = $this->getMockBuilder(Rbac::class)->setMethods(['checkPermissions'])->getMock();
        $rbac->expects($this->once())
            ->method('checkPermissions')
            ->with($identity->getOriginalData()->toArray())
            ->will($this->returnValue(false));
        $this->AuthLink->getView()->setRequest($this->AuthLink->getView()->getRequest()->withAttribute('rbac', $rbac));
        $result = $this->AuthLink->link(
            'title',
            ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'profile'],
            ['before' => 'before_', 'after' => '_after', 'class' => 'link-class']
        );
        $this->assertFalse($result);
    }

    /**
     * Test link
     *
     * @return void
     */
    public function testLinkAuthorizedHappy()
    {
        $user = new User([
            'id' => '00000000-0000-0000-0000-000000000001',
            'password' => '12345'
        ]);
        $identity = new Identity($user);
        $this->AuthLink->getView()->setRequest($this->AuthLink->getView()->getRequest()->withAttribute('identity', $identity));
        $rbac = $this->getMockBuilder(Rbac::class)->setMethods(['checkPermissions'])->getMock();
        $rbac->expects($this->once())
            ->method('checkPermissions')
            ->with($identity->getOriginalData()->toArray())
            ->will($this->returnValue(true));
        $this->AuthLink->getView()->setRequest($this->AuthLink->getView()->getRequest()->withAttribute('rbac', $rbac));
        $link = $this->AuthLink->link(
            'title',
            ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'profile'],
            ['before' => 'before_', 'after' => '_after', 'class' => 'link-class']
        );
        $this->assertSame('before_<a href="/profile" class="link-class">title</a>_after', $link);
    }

    /**
     * Test link
     *
     * @return void
     */
    public function testLinkAuthorizedAllowedTrue()
    {
        $link = $this->AuthLink->link('title', '/', ['allowed' => true, 'before' => 'before_', 'after' => '_after', 'class' => 'link-class']);
        $this->assertSame('before_<a href="/" class="link-class">title</a>_after', $link);
    }

    /**
     * Test link
     *
     * @return void
     */
    public function testLinkAuthorizedAllowedFalse()
    {
        $link = $this->AuthLink->link('title', '/', ['allowed' => false, 'before' => 'before_', 'after' => '_after', 'class' => 'link-class']);
        $this->assertFalse($link);
    }

    /**
     * Test isAuthorized
     *
     * @return void
     */
    public function testIsAuthorized()
    {
        $user = new User([
            'id' => '00000000-0000-0000-0000-000000000001',
            'password' => '12345'
        ]);
        $identity = new Identity($user);
        $this->AuthLink->getView()->setRequest($this->AuthLink->getView()->getRequest()->withAttribute('identity', $identity));
        $rbac = $this->getMockBuilder(Rbac::class)->setMethods(['checkPermissions'])->getMock();
        $rbac->expects($this->once())
            ->method('checkPermissions')
            ->with($identity->getOriginalData()->toArray())
            ->will($this->returnValue(true));
        $this->AuthLink->getView()->setRequest($this->AuthLink->getView()->getRequest()->withAttribute('rbac', $rbac));
        $result = $this->AuthLink->isAuthorized(['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'profile']);
        $this->assertTrue($result);
    }
    /**
     * Test isAuthorized
     *
     * @return void
     */
    public function testIsAuthorizedFalse()
    {
        $user = new User([
            'id' => '00000000-0000-0000-0000-000000000001',
            'password' => '12345'
        ]);
        $identity = new Identity($user);
        $this->AuthLink->getView()->setRequest($this->AuthLink->getView()->getRequest()->withAttribute('identity', $identity));
        $rbac = $this->getMockBuilder(Rbac::class)->setMethods(['checkPermissions'])->getMock();
        $rbac->expects($this->once())
            ->method('checkPermissions')
            ->with($identity->getOriginalData()->toArray())
            ->will($this->returnValue(false));
        $this->AuthLink->getView()->setRequest($this->AuthLink->getView()->getRequest()->withAttribute('rbac', $rbac));
        $result = $this->AuthLink->isAuthorized(['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'profile']);
        $this->assertFalse($result);
    }
}

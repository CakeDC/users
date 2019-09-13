<?php
/**
 * Copyright 2010 - 2019, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2018, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Test\TestCase\View\Helper;

use CakeDC\Users\View\Helper\AuthLinkHelper;
use Cake\Http\ServerRequest;
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
        $this->AuthLink = $this->getMockBuilder(AuthLinkHelper::class)
            ->setMethods(['isAuthorized'])
            ->setConstructorArgs([$view])
            ->getMock();
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
    public function testLinkFalseWithMock()
    {
        $this->AuthLink->expects($this->once())
            ->method('isAuthorized')
            ->with(
                $this->equalTo(['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'profile'])
            )
            ->will($this->returnValue(false));
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
        $this->AuthLink->expects($this->once())
            ->method('isAuthorized')
            ->with(
                $this->equalTo(['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'profile'])
            )
            ->will($this->returnValue(true));
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
     * Test getRequest method
     *
     * @retunr void
     */
    public function testGetRequest()
    {
        $actual = $this->AuthLink->getRequest();
        $this->assertInstanceOf(ServerRequest::class, $actual);
    }
}

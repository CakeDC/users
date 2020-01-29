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

namespace CakeDC\Users\Test\TestCase\View\Helper;

use Cake\Http\ServerRequest;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use Cake\View\View;
use CakeDC\Users\View\Helper\AuthLinkHelper;

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
    public function setUp(): void
    {
        parent::setUp();
        $view = new View(new ServerRequest());
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
    public function tearDown(): void
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
        $this->assertEmpty($result);
    }

    /**
     * Test link
     *
     * @return void
     */
    public function testLinkAuthorizedHappy()
    {
        Router::connect('/profile', [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'profile',
        ]);
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
        $this->assertEmpty($link);
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

    /**
     * Test post link with delete user method
     * Logged as Super user
     *
     * @return void
     */
    public function testPostLinkAuthorizedAllowedTrueLoggedAsAdmin()
    {
        $url = [
            'prefix' => false,
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'delete',
            '00000000-0000-0000-0000-000000000010',
        ];

        $this->AuthLink->expects($this->any())
            ->method('allowMethod')
            ->with(['post', 'delete'])
            ->will($this->returnValue(true));

        $link = $this->AuthLink->postLink('Post Link Title', $url, [
                'allowed' => true,
                'class' => 'link-class',
                'confirm' => 'confirmation message',
            ]);

        $this->assertContains('confirmation message', $link);
        $this->assertContains('Post Link Title', $link);
    }

    /**
     * Test post link with delete user method
     * Logged as normal user
     *
     * @return void
     */
    public function testPostLinkAuthorizedAllowedFalseLoggedWithoutRole()
    {
        $url = [
            'prefix' => false,
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'delete',
            '00000000-0000-0000-0000-000000000010',
        ];

        $this->AuthLink->expects($this->once())
            ->method('isAuthorized')
            ->with(
                $this->equalTo($url)
            )
            ->will($this->returnValue(false));

        $link = $this->AuthLink->postLink('Post Link Title', $url, [
                'allowed' => true,
                'class' => 'link-class',
                'confirm' => 'confirmation message',
            ]);

        $this->assertFalse($link);
    }

    /**
     * Test post link with delete user method
     *
     * @return void
     */
    public function testPostLinkAuthorizedAllowedFalse()
    {
        $url = [
            'prefix' => false,
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'delete',
            '00000000-0000-0000-0000-000000000010',
        ];

        $this->AuthLink->expects($this->once())
            ->method('isAuthorized')
            ->with(
                $this->equalTo($url)
            )
            ->will($this->returnValue(false));

        $link = $this->AuthLink->postLink('Post Link Title', $url, [
            'allowed' => true,
            'class' => 'link-class',
            'confirm' => 'confirmation message',
        ]);
        $this->assertFalse($link);
    }
}

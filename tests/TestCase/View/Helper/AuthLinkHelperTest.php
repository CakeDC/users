<?php
declare(strict_types=1);
/**
 * Copyright 2010 - 2017, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2017, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Test\TestCase\View\Helper;

use Cake\Event\Event;
use Cake\Event\EventManager;
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
        $link = $this->AuthLink->link('title', ['controller' => 'noaccess']);
        $this->assertEmpty($link);
    }

    /**
     * Test link
     *
     * @return void
     */
    public function testLinkAuthorized()
    {
        $view = new View();
        $eventManagerMock = $this->getMockBuilder('Cake\Event\EventManager')
            ->setMethods(['dispatch'])
            ->getMock();
        EventManager::instance($eventManagerMock);
        $this->AuthLink = new AuthLinkHelper($view);
        $result = new Event('dispatch-result');
        $result->setResult(true);
        $eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->will($this->returnValue($result));

        $link = $this->AuthLink->link('title', '/', ['before' => 'before_', 'after' => '_after', 'class' => 'link-class']);
        $this->assertSame('before_<a href="/" class="link-class">title</a>_after', $link);
    }

    /**
     * Test link
     *
     * @return void
     */
    public function testLinkAuthorizedAllowedTrue()
    {
        $view = new View();
        $eventManagerMock = $this->getMockBuilder('Cake\Event\EventManager')
            ->setMethods(['dispatch'])
            ->getMock();
        EventManager::instance($eventManagerMock);
        $this->AuthLink = new AuthLinkHelper($view);
        $result = new Event('dispatch-result');
        $result->setResult(true);
        $eventManagerMock->expects($this->never())
            ->method('dispatch');

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
        $view = new View();
        $eventManagerMock = $this->getMockBuilder('Cake\Event\EventManager')
            ->setMethods(['dispatch'])
            ->getMock();
        $view->getEventManager($eventManagerMock);
        $this->AuthLink = new AuthLinkHelper($view);
        $result = new Event('dispatch-result');
        $eventManagerMock->expects($this->never())
            ->method('dispatch');
        $link = $this->AuthLink->link('title', '/', ['allowed' => false, 'before' => 'before_', 'after' => '_after', 'class' => 'link-class']);
        $this->assertEmpty($link);
    }

    /**
     * Test isAuthorized
     *
     * @return void
     */
    public function testIsAuthorized()
    {
        $view = new View();
        $eventManagerMock = $this->getMockBuilder('Cake\Event\EventManager')
            ->setMethods(['dispatch'])
            ->getMock();
        EventManager::instance($eventManagerMock);
        $this->AuthLink = new AuthLinkHelper($view);
        $result = new Event('dispatch-result');
        $result->setResult(true);
        $eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->will($this->returnValue($result));

        $result = $this->AuthLink->isAuthorized(['controller' => 'MyController', 'action' => 'myAction']);
        $this->assertTrue($result);
    }
}

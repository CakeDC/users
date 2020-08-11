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

namespace CakeDC\Users\Test\TestCase\Controller\Component;

use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use CakeDC\Users\Controller\Component\SetupComponent;

/**
 * Class SetupComponentTest
 *
 * @package CakeDC\Users\Test\TestCase\Controller\Component
 */
class SetupComponentTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \CakeDC\Users\Controller\Component\SetupComponent
     */
    public $Component;

    /**
     * @var \Cake\Controller\Controller
     */
    public $Controller;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->Controller = new Controller();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->Controller, $this->Component);

        parent::tearDown();
    }

    /**
     * Data provider for testInitialization
     *
     * @return array
     */
    public function dataProviderInitialization()
    {
        return [
            [true, true, true],
            [false, true, true],
            [true, false, true],
            [true, true, false],
            [false, false, false],
        ];
    }

    /**
     * Test initial setup
     *
     * @param bool $authentication Should use authentication component
     * @param booll $authorization Should use authorization component
     * @param booll $oneTimePass Should use OneTimePassword component
     * @throws \Exception
     * @dataProvider dataProviderInitialization
     * @return void
     */
    public function testInitialization($authentication, $authorization, $oneTimePass)
    {
        Configure::write('Auth.AuthenticationComponent.load', $authentication);
        Configure::write('Auth.AuthorizationComponent.enable', $authorization);
        Configure::write('OneTimePasswordAuthenticator.login', $oneTimePass);
        $registry = new ComponentRegistry($this->Controller);
        $this->Component = new SetupComponent($registry);
        $this->Component->initialize([]);
        $this->assertSame($authentication, $this->Controller->components()->has('Authentication'));
        $this->assertSame($authorization, $this->Controller->components()->has('Authorization'));
        $this->assertSame($oneTimePass, $this->Controller->components()->has('OneTimePasswordAuthenticator'));
    }
}

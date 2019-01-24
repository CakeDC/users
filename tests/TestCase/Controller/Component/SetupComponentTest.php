<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     3.3.0
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */

namespace CakeDC\Users\Test\TestCase\Controller\Component;

use Cake\Controller\Controller;
use Cake\Core\Configure;
use CakeDC\Users\Controller\Component\SetupComponent;
use Cake\Controller\ComponentRegistry;
use Cake\TestSuite\TestCase;

/**
 * Class SetupComponentTest
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
    public function setUp()
    {
        parent::setUp();
        $this->Controller = new Controller();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
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
            [false, false, false]
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

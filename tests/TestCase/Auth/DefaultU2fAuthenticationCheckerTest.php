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
namespace CakeDC\Users\Test\TestCase\Auth;

use CakeDC\Users\Auth\DefaultU2fAuthenticationChecker;
use Cake\Core\Configure;
use Cake\Http\Exception\BadRequestException;
use Cake\TestSuite\TestCase;

/**
 * Test case for DefaultU2fAuthenticationChecker class
 *
 * @package CakeDC\Users\Test\TestCase\Auth
 */
class DefaultU2fAuthenticationCheckerTest extends TestCase
{
    /**
     * Test isEnabled method
     *
     * @return void
     */
    public function testIsEnabled()
    {
        Configure::write('U2f.enabled', false);
        $Checker = new DefaultU2fAuthenticationChecker();
        $this->assertFalse($Checker->isEnabled());

        Configure::write('U2f.enabled', true);
        $Checker = new DefaultU2fAuthenticationChecker();
        $this->assertTrue($Checker->isEnabled());

        Configure::delete('U2f.enabled');
        $Checker = new DefaultU2fAuthenticationChecker();
        $this->assertTrue($Checker->isEnabled());
    }

    /**
     * Test isRequired method
     *
     * @return void
     */
    public function testIsRequired()
    {
        Configure::write('U2f.enabled', false);
        $Checker = new DefaultU2fAuthenticationChecker();
        $this->assertFalse($Checker->isRequired(['id' => 10]));

        Configure::write('U2f.enabled', true);
        $Checker = new DefaultU2fAuthenticationChecker();
        $this->assertTrue($Checker->isRequired(['id' => 10]));

        Configure::delete('U2f.enabled');
        $Checker = new DefaultU2fAuthenticationChecker();
        $this->assertTrue($Checker->isRequired(['id' => 10]));

        $Checker = new DefaultU2fAuthenticationChecker();
        $this->assertFalse($Checker->isRequired([]));
    }
}

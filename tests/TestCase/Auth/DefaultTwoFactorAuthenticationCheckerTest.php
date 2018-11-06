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
namespace CakeDC\Users\Test\TestCase\Auth;

use CakeDC\Users\Auth\DefaultTwoFactorAuthenticationChecker;
use Cake\Core\Configure;
use Cake\Network\Exception\BadRequestException;
use Cake\TestSuite\TestCase;

/**
 * Test case for DefaultTwoFactorAuthenticationChecker class
 *
 * @package CakeDC\Users\Test\TestCase\Auth
 */
class DefaultTwoFactorAuthenticationCheckerTest extends TestCase
{
    /**
     * Test isEnabled method
     *
     * @return void
     */
    public function testIsEnabled()
    {
        Configure::write('Users.GoogleAuthenticator.login', false);
        $Checker = new DefaultTwoFactorAuthenticationChecker();
        $this->assertFalse($Checker->isEnabled());

        Configure::write('Users.GoogleAuthenticator.login', true);
        $Checker = new DefaultTwoFactorAuthenticationChecker();
        $this->assertTrue($Checker->isEnabled());

        Configure::delete('Users.GoogleAuthenticator.login');
        $Checker = new DefaultTwoFactorAuthenticationChecker();
        $this->assertTrue($Checker->isEnabled());
    }

    /**
     * Test isRequired method
     *
     * @return void
     */
    public function testIsRequired()
    {
        Configure::write('Users.GoogleAuthenticator.login', false);
        $Checker = new DefaultTwoFactorAuthenticationChecker();
        $this->assertFalse($Checker->isRequired(['id' => 10]));

        Configure::write('Users.GoogleAuthenticator.login', true);
        $Checker = new DefaultTwoFactorAuthenticationChecker();
        $this->assertTrue($Checker->isRequired(['id' => 10]));

        Configure::delete('Users.GoogleAuthenticator.login');
        $Checker = new DefaultTwoFactorAuthenticationChecker();
        $this->assertTrue($Checker->isRequired(['id' => 10]));

        $Checker = new DefaultTwoFactorAuthenticationChecker();
        $this->assertFalse($Checker->isRequired([]));
    }
}

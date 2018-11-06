<?php
namespace CakeDC\Users\Test\TestCase\Auth;

use Cake\Core\Configure;
use CakeDC\Users\Auth\DefaultTwoFactorAuthenticationChecker;
use CakeDC\Users\Auth\TwoFactorAuthenticationCheckerFactory;
use CakeDC\Users\Auth\TwoFactorAuthenticationCheckerInterface;
use Cake\TestSuite\TestCase;

class TwoFactorAuthenticationCheckerFactoryTest extends TestCase
{
    /**
     * Test getChecker method
     *
     * @return void
     */
    public function testGetChecker()
    {
        $result = (new TwoFactorAuthenticationCheckerFactory())->build();
        $this->assertInstanceOf(DefaultTwoFactorAuthenticationChecker::class, $result);
    }

    /**
     * Test getChecker method
     *
     * @return void
     */
    public function testGetCheckerInvalidInterface()
    {
        Configure::write('GoogleAuthenticator.checker', 'stdClass');
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid config for 'GoogleAuthenticator.checker', 'stdClass' does not implement 'CakeDC\Users\Auth\TwoFactorAuthenticationCheckerInterface'");
        $result = (new TwoFactorAuthenticationCheckerFactory())->build();
    }
}

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

namespace CakeDC\Users\Test\TestCase\Controller\Traits;

use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use ReflectionMethod;

class ReCaptchaTraitTest extends TestCase
{
    /**
     * setUp callback
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->Trait = $this->getMockBuilder('CakeDC\Users\Controller\Traits\ReCaptchaTrait')
            ->setMethods(['_getReCaptchaInstance'])
            ->getMockForTrait();
    }

    /**
     * tearDown callback
     *
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * testValidateValidReCaptcha
     *
     * @return void
     */
    public function testValidateValidReCaptcha()
    {
        $ReCaptcha = $this->getMockBuilder('ReCaptcha\ReCaptcha')
                ->setMethods(['verify'])
                ->disableOriginalConstructor()
                ->getMock();
        $Response = $this->getMockBuilder('ReCaptcha\Response')
                ->setMethods(['isSuccess'])
                ->disableOriginalConstructor()
                ->getMock();
        $Response->expects($this->once())
            ->method('isSuccess')
            ->will($this->returnValue(true));
        $ReCaptcha->expects($this->once())
            ->method('verify')
            ->with('value')
            ->will($this->returnValue($Response));
        $this->Trait->expects($this->once())
            ->method('_getReCaptchaInstance')
            ->will($this->returnValue($ReCaptcha));
        $actual = $this->Trait->validateReCaptcha('value', '255.255.255.255');
        $this->assertTrue($actual);
    }

    /**
     * testValidateInvalidReCaptcha
     *
     * @return void
     */
    public function testValidateInvalidReCaptcha()
    {
        $ReCaptcha = $this->getMockBuilder('ReCaptcha\ReCaptcha')
                ->setMethods(['verify'])
                ->disableOriginalConstructor()
                ->getMock();
        $Response = $this->getMockBuilder('ReCaptcha\Response')
                ->setMethods(['isSuccess'])
                ->disableOriginalConstructor()
                ->getMock();
        $Response->expects($this->once())
            ->method('isSuccess')
            ->will($this->returnValue(false));
        $ReCaptcha->expects($this->once())
            ->method('verify')
            ->with('invalid')
            ->will($this->returnValue($Response));
        $this->Trait->expects($this->once())
            ->method('_getReCaptchaInstance')
            ->will($this->returnValue($ReCaptcha));
        $actual = $this->Trait->validateReCaptcha('invalid', '255.255.255.255');
        $this->assertFalse($actual);
    }

    public function testGetRecaptchaInstance()
    {
        Configure::write('Users.reCaptcha.secret', 'secret');
        $trait = $this->getMockBuilder('CakeDC\Users\Controller\Traits\ReCaptchaTrait')->getMockForTrait();
        $method = new ReflectionMethod(get_class($trait), '_getReCaptchaInstance');
        $method->setAccessible(true);
        $method->invokeArgs($trait, []);
        $this->assertNotEmpty($method->invoke($trait));
    }

    public function testGetRecaptchaInstanceNull()
    {
        $trait = $this->getMockBuilder('CakeDC\Users\Controller\Traits\ReCaptchaTrait')->getMockForTrait();
        $method = new ReflectionMethod(get_class($trait), '_getReCaptchaInstance');
        $method->setAccessible(true);
        $method->invokeArgs($trait, []);
        $this->assertNull($method->invoke($trait));
    }

    public function testValidateReCaptchaFalse()
    {
        $ReCaptcha = $this->getMockBuilder('ReCaptcha\ReCaptcha')
            ->setMethods(['verify'])
            ->disableOriginalConstructor()
            ->getMock();
        $Response = $this->getMockBuilder('ReCaptcha\Response')
            ->setMethods(['isSuccess'])
            ->disableOriginalConstructor()
            ->getMock();
        $Response->expects($this->once())
            ->method('isSuccess')
            ->will($this->returnValue(false));
        $ReCaptcha->expects($this->once())
            ->method('verify')
            ->with('value')
            ->will($this->returnValue($Response));
        $this->Trait->expects($this->once())
            ->method('_getReCaptchaInstance')
            ->will($this->returnValue($ReCaptcha));

        $this->assertFalse($this->Trait->validateReCaptcha('value', '255.255.255.255'));
    }
}

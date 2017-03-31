<?php
/**
 * Copyright 2010 - 2017, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2017, Cake Development Corporation (https://www.cakedc.com)
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
    public function setUp()
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
    public function tearDown()
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
        $Response->expects($this->any())
            ->method('isSuccess')
            ->will($this->returnValue(true));
        $ReCaptcha->expects($this->any())
            ->method('verify')
            ->with('value')
            ->will($this->returnValue($Response));
        $this->Trait->expects($this->any())
            ->method('_getReCaptchaInstance')
            ->will($this->returnValue($ReCaptcha));
        $this->Trait->validateReCaptcha('value', '255.255.255.255');
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
        $Response->expects($this->any())
            ->method('isSuccess')
            ->will($this->returnValue(false));
        $ReCaptcha->expects($this->any())
            ->method('verify')
            ->with('invalid')
            ->will($this->returnValue($Response));
        $this->Trait->expects($this->any())
            ->method('_getReCaptchaInstance')
            ->will($this->returnValue($ReCaptcha));
        $this->Trait->validateReCaptcha('invalid', '255.255.255.255');
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
        $trait = $this->getMockBuilder('CakeDC\Users\Controller\Traits\ReCaptchaTrait')->getMockForTrait();
        $this->assertFalse($this->Trait->validateReCaptcha('value', '255.255.255.255'));
    }
}

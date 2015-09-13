<?php
/**
 * Copyright 2010 - 2015, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2015, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Test\TestCase\Controller\Traits;

use Cake\TestSuite\TestCase;

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
        $ReCaptcha = $this->getMock('ReCaptcha\ReCaptcha', ['verify'], [], '', false);
        $Response = $this->getMock('ReCaptcha\Response', ['isSuccess'], [], '', false);
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
        $ReCaptcha = $this->getMock('ReCaptcha\ReCaptcha', ['verify'], [], '', false);
        $Response = $this->getMock('ReCaptcha\Response', ['isSuccess'], [], '', false);
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
}

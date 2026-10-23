<?php
declare(strict_types=1);

/**
 * Copyright 2010 - 2026, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2026, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Test\TestCase\Controller\Traits;

use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use CakeDC\Users\Controller\Traits\ReCaptchaTrait;
use ReCaptcha\ReCaptcha;
use ReCaptcha\Response;
use ReflectionMethod;

class ReCaptchaTraitTest extends TestCase
{
    /**
     * @var object
     */
    public object $Trait;

    /**
     * setUp callback
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        Configure::delete('Users.reCaptcha.secret');
        $this->Trait = new class {
            use ReCaptchaTrait;

            public ?ReCaptcha $reCaptchaInstance = null;

            protected function getReCaptchaInstance(): ?ReCaptcha
            {
                return $this->reCaptchaInstance;
            }
        };
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
    public function testValidateValidReCaptcha(): void
    {
        $ReCaptcha = $this->getMockBuilder(ReCaptcha::class)
                ->onlyMethods(['verify'])
                ->disableOriginalConstructor()
                ->getMock();
        $Response = new Response(true);
        $ReCaptcha->expects($this->once())
            ->method('verify')
            ->with('value')
            ->willReturn($Response);
        $this->Trait->reCaptchaInstance = $ReCaptcha;
        $actual = $this->Trait->validateReCaptcha('value', '255.255.255.255');
        $this->assertTrue($actual);
    }

    /**
     * testValidateInvalidReCaptcha
     *
     * @return void
     */
    public function testValidateInvalidReCaptcha(): void
    {
        $ReCaptcha = $this->getMockBuilder(ReCaptcha::class)
                ->onlyMethods(['verify'])
                ->disableOriginalConstructor()
                ->getMock();
        $Response = new Response(false);
        $ReCaptcha->expects($this->once())
            ->method('verify')
            ->with('invalid')
            ->willReturn($Response);
        $this->Trait->reCaptchaInstance = $ReCaptcha;
        $actual = $this->Trait->validateReCaptcha('invalid', '255.255.255.255');
        $this->assertFalse($actual);
    }

    public function testGetRecaptchaInstance(): void
    {
        Configure::write('Users.reCaptcha.secret', 'secret');
        $trait = new class {
            use ReCaptchaTrait;
        };
        $method = new ReflectionMethod($trait, 'getReCaptchaInstance');
        $method->invokeArgs($trait, []);
        $this->assertNotEmpty($method->invoke($trait));
    }

    public function testGetRecaptchaInstanceNull(): void
    {
        $trait = new class {
            use ReCaptchaTrait;
        };
        $method = new ReflectionMethod($trait, 'getReCaptchaInstance');
        $method->invokeArgs($trait, []);
        $this->assertNull($method->invoke($trait));
    }

    public function testValidateReCaptchaFalse(): void
    {
        $ReCaptcha = $this->getMockBuilder(ReCaptcha::class)
            ->onlyMethods(['verify'])
            ->disableOriginalConstructor()
            ->getMock();
        $Response = new Response(false);
        $ReCaptcha->expects($this->once())
            ->method('verify')
            ->with('value')
            ->willReturn($Response);
        $this->Trait->reCaptchaInstance = $ReCaptcha;

        $this->assertFalse($this->Trait->validateReCaptcha('value', '255.255.255.255'));
    }
}

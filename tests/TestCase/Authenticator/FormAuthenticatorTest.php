<?php

use Authentication\Authenticator\Result;
use Authentication\Identifier\IdentifierCollection;
use Authentication\Identifier\IdentifierInterface;
use Cake\Core\Configure;
use Cake\Http\Client\Response;
use Cake\Http\ServerRequestFactory;
use Cake\TestSuite\TestCase;
use CakeDC\Users\Authenticator\FormAuthenticator;

class FormAuthenticatorTest extends TestCase
{
    /**
     * testAuthenticate
     *
     * @return void
     */
    public function testAuthenticateBaseFailed()
    {

        $identifiers = new IdentifierCollection([
            'Authentication.Password'
        ]);

        $BaseAuthenticator = $this->getMockBuilder(\Authentication\Authenticator\FormAuthenticator::class)
            ->setConstructorArgs([$identifiers])
            ->setMethods(['authenticate'])
            ->getMock();
        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/testpath'],
            [],
            ['username' => 'marcelo', 'password' => 'password', 'g-recaptcha-response' => 'BD-S2333-156465897897']
        );
        $response = new Response();

        $baseResult = new Result(
            null,
            Result::FAILURE_OTHER
        );
        $BaseAuthenticator->expects($this->once())
            ->method('authenticate')
            ->with($request, $response)
            ->will($this->returnValue($baseResult));

        $Authenticator = $this->getMockBuilder(FormAuthenticator::class)->setConstructorArgs([
            $identifiers,
            [
                'fields' => [
                    IdentifierInterface::CREDENTIAL_USERNAME => 'email',
                    IdentifierInterface::CREDENTIAL_PASSWORD => 'password'
                ]
            ]
        ])->setMethods(['createBaseAuthenticator', 'validateReCaptcha'])->getMock();

        Configure::write('Users.reCaptcha.login', true);
        $Authenticator->expects($this->once())
            ->method('createBaseAuthenticator')
            ->with(
                $this->equalTo($identifiers),
                $this->equalTo([
                    'fields' => [
                        IdentifierInterface::CREDENTIAL_USERNAME => 'email',
                        IdentifierInterface::CREDENTIAL_PASSWORD => 'password'
                    ]
                ])
            )->will($this->returnValue($BaseAuthenticator));

        $Authenticator->expects($this->never())
            ->method('validateReCaptcha');

        $result = $Authenticator->authenticate($request, $response);
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::FAILURE_OTHER, $result->getStatus());
        $this->assertSame($baseResult, $result);
        $this->assertSame($baseResult, $Authenticator->getLastResult());
    }

    /**
     * testAuthenticate
     *
     * @return void
     */
    public function testAuthenticate()
    {

        $identifiers = new IdentifierCollection([
            'Authentication.Password'
        ]);

        $BaseAuthenticator = $this->getMockBuilder(\Authentication\Authenticator\FormAuthenticator::class)
            ->setConstructorArgs([$identifiers])
            ->setMethods(['authenticate'])
            ->getMock();
        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/testpath'],
            [],
            ['username' => 'marcelo', 'password' => 'password', 'g-recaptcha-response' => 'BD-S2333-156465897897']
        );
        $response = new Response();

        $baseResult = new Result(
            [
                'id' => '42',
                'username' => 'marcelo',
                'role' => 'user'
            ],
            Result::SUCCESS
        );
        $BaseAuthenticator->expects($this->once())
            ->method('authenticate')
            ->with($request, $response)
            ->will($this->returnValue($baseResult));

        $Authenticator = $this->getMockBuilder(FormAuthenticator::class)->setConstructorArgs([
            $identifiers,
            [
                'fields' => [
                    IdentifierInterface::CREDENTIAL_USERNAME => 'email',
                    IdentifierInterface::CREDENTIAL_PASSWORD => 'password'
                ]
            ]
        ])->setMethods(['createBaseAuthenticator', 'validateReCaptcha'])->getMock();

        Configure::write('Users.reCaptcha.login', true);
        $Authenticator->expects($this->once())
            ->method('createBaseAuthenticator')
            ->with(
                $this->equalTo($identifiers),
                $this->equalTo([
                    'fields' => [
                        IdentifierInterface::CREDENTIAL_USERNAME => 'email',
                        IdentifierInterface::CREDENTIAL_PASSWORD => 'password'
                    ]
                ])
            )->will($this->returnValue($BaseAuthenticator));

        $Authenticator->expects($this->once())
            ->method('validateReCaptcha')
            ->with(
                $this->equalTo('BD-S2333-156465897897')
            )
            ->will($this->returnValue(true));

        $result = $Authenticator->authenticate($request, $response);
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::SUCCESS, $result->getStatus());
        $this->assertSame($baseResult, $result);
        $this->assertSame($baseResult, $Authenticator->getLastResult());
    }

    /**
     * testAuthenticate
     *
     * @return void
     */
    public function testAuthenticateNotRequiredReCaptcha()
    {

        $identifiers = new IdentifierCollection([
            'Authentication.Password'
        ]);

        $BaseAuthenticator = $this->getMockBuilder(\Authentication\Authenticator\FormAuthenticator::class)
            ->setConstructorArgs([$identifiers])
            ->setMethods(['authenticate'])
            ->getMock();
        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/testpath'],
            [],
            ['username' => 'marcelo', 'password' => 'password', 'g-recaptcha-response' => 'BD-S2333-156465897897']
        );
        $response = new Response();

        $baseResult = new Result(
            [
                'id' => '42',
                'username' => 'marcelo',
                'role' => 'user'
            ],
            Result::SUCCESS
        );
        $BaseAuthenticator->expects($this->once())
            ->method('authenticate')
            ->with($request, $response)
            ->will($this->returnValue($baseResult));

        $Authenticator = $this->getMockBuilder(FormAuthenticator::class)->setConstructorArgs([
            $identifiers,
            [
                'fields' => [
                    IdentifierInterface::CREDENTIAL_USERNAME => 'email',
                    IdentifierInterface::CREDENTIAL_PASSWORD => 'password'
                ]
            ]
        ])->setMethods(['createBaseAuthenticator', 'validateReCaptcha'])->getMock();

        Configure::write('Users.reCaptcha.login', false);
        $Authenticator->expects($this->once())
            ->method('createBaseAuthenticator')
            ->with(
                $this->equalTo($identifiers),
                $this->equalTo([
                    'fields' => [
                        IdentifierInterface::CREDENTIAL_USERNAME => 'email',
                        IdentifierInterface::CREDENTIAL_PASSWORD => 'password'
                    ]
                ])
            )->will($this->returnValue($BaseAuthenticator));

        $Authenticator->expects($this->never())
            ->method('validateReCaptcha');

        $result = $Authenticator->authenticate($request, $response);
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::SUCCESS, $result->getStatus());
        $this->assertSame($baseResult, $result);
        $this->assertSame($baseResult, $Authenticator->getLastResult());
    }

    /**
     * testAuthenticate
     *
     * @return void
     */
    public function testAuthenticateInvalidRecaptcha()
    {

        $identifiers = new IdentifierCollection([
            'Authentication.Password'
        ]);

        $BaseAuthenticator = $this->getMockBuilder(\Authentication\Authenticator\FormAuthenticator::class)
            ->setConstructorArgs([$identifiers])
            ->setMethods(['authenticate'])
            ->getMock();
        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/testpath'],
            [],
            ['username' => 'marcelo', 'password' => 'password', 'g-recaptcha-response' => 'BD-S2333-156465897897']
        );
        $response = new Response();

        $baseResult = new Result(
            [
                'id' => '42',
                'username' => 'marcelo',
                'role' => 'user'
            ],
            Result::SUCCESS
        );
        $BaseAuthenticator->expects($this->once())
            ->method('authenticate')
            ->with($request, $response)
            ->will($this->returnValue($baseResult));

        $Authenticator = $this->getMockBuilder(FormAuthenticator::class)->setConstructorArgs([
            $identifiers,
            [
                'fields' => [
                    IdentifierInterface::CREDENTIAL_USERNAME => 'email',
                    IdentifierInterface::CREDENTIAL_PASSWORD => 'password'
                ]
            ]
        ])->setMethods(['createBaseAuthenticator', 'validateReCaptcha'])->getMock();

        Configure::write('Users.reCaptcha.login', true);
        $Authenticator->expects($this->once())
            ->method('createBaseAuthenticator')
            ->with(
                $this->equalTo($identifiers),
                $this->equalTo([
                    'fields' => [
                        IdentifierInterface::CREDENTIAL_USERNAME => 'email',
                        IdentifierInterface::CREDENTIAL_PASSWORD => 'password'
                    ]
                ])
            )->will($this->returnValue($BaseAuthenticator));

        $Authenticator->expects($this->once())
            ->method('validateReCaptcha')
            ->with(
                $this->equalTo('BD-S2333-156465897897')
            )
            ->will($this->returnValue(false));

        $result = $Authenticator->authenticate($request, $response);
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(FormAuthenticator::FAILURE_INVALID_RECAPTCHA, $result->getStatus());
        $this->assertNull($result->getData());
        $this->assertSame($result, $Authenticator->getLastResult());
    }
}

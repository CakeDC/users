<?php

declare(strict_types=1);

namespace CakeDC\Users\Test\TestCase;

use Cake\Http\MiddlewareQueue;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use CakeDC\Users\UsersPlugin;

/**
 * UsersPlugin Test Case
 */
class UsersPluginTest extends TestCase
{
    /**
     * @var \CakeDC\Users\UsersPlugin
     */
    protected $Plugin;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->Plugin = new UsersPlugin();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->Plugin);
        parent::tearDown();
    }

    /**
     * Test middleware method
     *
     * @return void
     */
    public function testMiddleware(): void
    {
        $middlewareQueue = new MiddlewareQueue();
        $result = $this->Plugin->middleware($middlewareQueue);
        $this->assertInstanceOf(MiddlewareQueue::class, $result);
    }

    /**
     * Test services method
     *
     * @return void
     */
    public function testServices(): void
    {
        $container = $this->getMockBuilder('Cake\Core\ContainerInterface')->getMock();

        $container->expects($this->exactly(2))
            ->method('has')
            ->willReturnMap([
                ['CakeDC\Users\Webauthn\AuthenticateAdapter', false],
                ['CakeDC\Users\Webauthn\RegisterAdapter', false],
            ]);

        $mockDefinition = $this->getMockBuilder('League\Container\Definition\DefinitionInterface')
            ->onlyMethods(['addArgument'])
            ->getMockForAbstractClass();

        $mockDefinition->expects($this->exactly(2))
            ->method('addArgument')
            ->with(ServerRequest::class)
            ->willReturnSelf();

        $container->expects($this->exactly(2))
            ->method('add')
            ->willReturn($mockDefinition);

        $this->Plugin->services($container);
    }
}

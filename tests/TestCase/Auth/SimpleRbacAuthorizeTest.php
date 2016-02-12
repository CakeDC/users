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

namespace CakeDC\Users\Test\TestCase\Auth;

use CakeDC\Users\Auth\SimpleRbacAuthorize;
use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\Event\EventManager;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\TestSuite\TestCase;
use Cake\Utility\Hash;
use ReflectionClass;

class SimpleRbacAuthorizeTest extends TestCase
{

    /**
     * @var SimpleRbacAuthorize
     */
    protected $simpleRbacAuthorize;

    protected $defaultPermissions = [
        //admin role allowed to use CakeDC\Users plugin actions
        [
            'role' => 'admin',
            'plugin' => '*',
            'controller' => '*',
            'action' => '*',
        ],
        //specific actions allowed for the user role in Users plugin
        [
            'role' => 'user',
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => ['profile', 'logout'],
        ],
        //all roles allowed to Pages/display
        [
            'role' => '*',
            'plugin' => null,
            'controller' => ['Pages'],
            'action' => ['display'],
        ],
    ];


    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    public function setUp()
    {
        $request = new Request();
        $response = new Response();

        $this->controller = $this->getMock(
            'Cake\Controller\Controller',
            null,
            [$request, $response]
        );
        $this->registry = new ComponentRegistry($this->controller);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    public function tearDown()
    {
        unset($this->simpleRbacAuthorize, $this->controller);
    }

    /**
     * @covers CakeDC\Users\Auth\SimpleRbacAuthorize::__construct
     */
    public function testConstruct()
    {
        //don't autoload config
        $this->simpleRbacAuthorize = new SimpleRbacAuthorize($this->registry, ['autoload_config' => false]);
        $this->assertEmpty($this->simpleRbacAuthorize->config('permissions'));
    }

    /**
     * test
     *
     * @return void
     */
    public function testLoadPermissions()
    {
        $this->simpleRbacAuthorize = $this->getMockBuilder('CakeDC\Users\Auth\SimpleRbacAuthorize')
                ->disableOriginalConstructor()
                ->getMock();
        $reflectedClass = new ReflectionClass($this->simpleRbacAuthorize);
        $loadPermissions = $reflectedClass->getMethod('_loadPermissions');
        $loadPermissions->setAccessible(true);
        $permissions = $loadPermissions->invoke($this->simpleRbacAuthorize, 'missing');
        $this->assertEquals($this->defaultPermissions, $permissions);
    }

    /**
     * @covers CakeDC\Users\Auth\SimpleRbacAuthorize::__construct
     */
    public function testConstructMissingPermissionsFile()
    {
        $this->simpleRbacAuthorize = $this->getMock(
            'CakeDC\Users\Auth\SimpleRbacAuthorize',
            null,
            [$this->registry, ['autoload_config' => 'does-not-exist']]
        );
        //we should have the default permissions
        $this->assertEquals($this->defaultPermissions, $this->simpleRbacAuthorize->config('permissions'));
    }

    protected function assertConstructorPermissions($instance, $config, $permissions)
    {
        $reflectedClass = new ReflectionClass($instance);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke($this->simpleRbacAuthorize, $this->registry, $config);

        //we should have the default permissions
        $resultPermissions = $this->simpleRbacAuthorize->config('permissions');
        $this->assertEquals($permissions, $resultPermissions);
    }

    /**
     * @covers CakeDC\Users\Auth\SimpleRbacAuthorize::__construct
     */
    public function testConstructPermissionsFileHappy()
    {
        $permissions = [[
            'controller' => 'Test',
            'action' => 'test'
        ]];
        $className = 'CakeDC\Users\Auth\SimpleRbacAuthorize';
        $this->simpleRbacAuthorize = $this->getMockBuilder($className)
                ->setMethods(['_loadPermissions'])
                ->disableOriginalConstructor()
                ->getMock();
        $this->simpleRbacAuthorize
                ->expects($this->once())
                ->method('_loadPermissions')
                ->with('permissions-happy')
                ->will($this->returnValue($permissions));
        $this->assertConstructorPermissions($className, ['autoload_config' => 'permissions-happy'], $permissions);
    }

    protected function preparePermissions($permissions)
    {
        $className = 'CakeDC\Users\Auth\SimpleRbacAuthorize';
        $simpleRbacAuthorize = $this->getMockBuilder($className)
                ->setMethods(['_loadPermissions'])
                ->disableOriginalConstructor()
                ->getMock();
        $simpleRbacAuthorize->config('permissions', $permissions);
        return $simpleRbacAuthorize;
    }

    /**
     * @dataProvider providerAuthorize
     */
    public function testAuthorize($permissions, $user, $requestParams, $expected, $msg = null)
    {
        $this->simpleRbacAuthorize = $this->preparePermissions($permissions);
        $request = new Request();
        $request->plugin = Hash::get($requestParams, 'plugin');
        $request->controller = $requestParams['controller'];
        $request->action = $requestParams['action'];
        $prefix = Hash::get($requestParams, 'prefix');
        if ($prefix) {
            $request->params = ['prefix' => $prefix];
        }

        $result = $this->simpleRbacAuthorize->authorize($user, $request);
        $this->assertSame($expected, $result, $msg);
    }

    public function providerAuthorize()
    {
        return [
            'happy-strict-all' => [
                //permissions
                [[
                    'plugin' => 'Tests',
                    'role' => 'test',
                    'controller' => 'Tests',
                    'action' => 'test',
                    'allowed' => true,
                ]],
                //user
                [
                    'id' => 1,
                    'username' => 'luke',
                    'role' => 'test',
                ],
                //request
                [
                    'plugin' => 'Tests',
                    'controller' => 'Tests',
                    'action' => 'test'
                ],
                //expected
                true
            ],
            'happy-strict-all-deny' => [
                //permissions
                [[
                    'plugin' => 'Tests',
                    'role' => 'test',
                    'controller' => 'Tests',
                    'action' => 'test',
                    'allowed' => false,
                ]],
                //user
                [
                    'id' => 1,
                    'username' => 'luke',
                    'role' => 'test',
                ],
                //request
                [
                    'plugin' => 'Tests',
                    'controller' => 'Tests',
                    'action' => 'test'
                ],
                //expected
                false
            ],
            'happy-plugin-null-allowed-null' => [
                //permissions
                [[
                    'role' => 'test',
                    'controller' => 'Tests',
                    'action' => 'test',
                ]],
                //user
                [
                    'id' => 1,
                    'username' => 'luke',
                    'role' => 'test',
                ],
                //request
                [
                    'plugin' => null,
                    'controller' => 'Tests',
                    'action' => 'test'
                ],
                //expected
                true
            ],
            'happy-plugin-asterisk' => [
                //permissions
                [[
                    'plugin' => '*',
                    'role' => 'test',
                    'controller' => 'Tests',
                    'action' => 'test',
                ]],
                //user
                [
                    'id' => 1,
                    'username' => 'luke',
                    'role' => 'test',
                ],
                //request
                [
                    'plugin' => 'Any',
                    'controller' => 'Tests',
                    'action' => 'test'
                ],
                //expected
                true
            ],
            'happy-plugin-asterisk-main-app' => [
                //permissions
                [[
                    'plugin' => '*',
                    'role' => 'test',
                    'controller' => 'Tests',
                    'action' => 'test',
                ]],
                //user
                [
                    'id' => 1,
                    'username' => 'luke',
                    'role' => 'test',
                ],
                //request
                [
                    'plugin' => null,
                    'controller' => 'Tests',
                    'action' => 'test'
                ],
                //expected
                true
            ],
            'happy-role-asterisk' => [
                //permissions
                [[
                    'role' => '*',
                    'controller' => 'Tests',
                    'action' => 'test',
                ]],
                //user
                [
                    'id' => 1,
                    'username' => 'luke',
                    'role' => 'any-role',
                ],
                //request
                [
                    'plugin' => null,
                    'controller' => 'Tests',
                    'action' => 'test'
                ],
                //expected
                true
            ],
            'happy-controller-asterisk' => [
                //permissions
                [[
                    'plugin' => 'Tests',
                    'role' => 'test',
                    'controller' => '*',
                    'action' => 'test',
                ]],
                //user
                [
                    'id' => 1,
                    'username' => 'luke',
                    'role' => 'test',
                ],
                //request
                [
                    'plugin' => 'Tests',
                    'controller' => 'Tests',
                    'action' => 'test'
                ],
                //expected
                true
            ],
            'happy-action-asterisk' => [
                //permissions
                [[
                    'plugin' => 'Tests',
                    'role' => 'test',
                    'controller' => 'Tests',
                    'action' => '*',
                ]],
                //user
                [
                    'id' => 1,
                    'username' => 'luke',
                    'role' => 'test',
                ],
                //request
                [
                    'plugin' => 'Tests',
                    'controller' => 'Tests',
                    'action' => 'any'
                ],
                //expected
                true
            ],
            'happy-some-asterisk-allowed' => [
                //permissions
                [[
                    'plugin' => '*',
                    'role' => 'test',
                    'controller' => '*',
                    'action' => '*',
                ]],
                //user
                [
                    'id' => 1,
                    'username' => 'luke',
                    'role' => 'test',
                ],
                //request
                [
                    'plugin' => 'Tests',
                    'controller' => 'Tests',
                    'action' => 'any'
                ],
                //expected
                true
            ],
            'happy-some-asterisk-deny' => [
                //permissions
                [[
                    'plugin' => '*',
                    'role' => 'test',
                    'controller' => '*',
                    'action' => '*',
                    'allowed' => false,
                ]],
                //user
                [
                    'id' => 1,
                    'username' => 'luke',
                    'role' => 'test',
                ],
                //request
                [
                    'plugin' => 'Tests',
                    'controller' => 'Tests',
                    'action' => 'any'
                ],
                //expected
                false
            ],
            'all-deny' => [
                //permissions
                [[
                    'plugin' => '*',
                    'role' => '*',
                    'controller' => '*',
                    'action' => '*',
                    'allowed' => false,
                ]],
                //user
                [
                    'id' => 1,
                    'username' => 'luke',
                    'role' => 'test',
                ],
                //request
                [
                    'plugin' => 'Any',
                    'controller' => 'Any',
                    'action' => 'any'
                ],
                //expected
                false
            ],
            'dasherized' => [
                //permissions
                [[
                    'plugin' => 'Tests',
                    'role' => 'test',
                    'controller' => 'TestTests',
                    'action' => 'TestAction',
                    'allowed' => true,
                ]],
                //user
                [
                    'id' => 1,
                    'username' => 'luke',
                    'role' => 'test',
                ],
                //request
                [
                    'plugin' => 'tests',
                    'controller' => 'test-tests',
                    'action' => 'test-action'
                ],
                //expected
                true
            ],
            'happy-array' => [
                //permissions
                [[
                    'plugin' => ['Tests'],
                    'role' => ['test'],
                    'controller' => ['Tests'],
                    'action' => ['one', 'two'],
                ]],
                //user
                [
                    'id' => 1,
                    'username' => 'luke',
                    'role' => 'test',
                ],
                //request
                [
                    'plugin' => 'Tests',
                    'controller' => 'Tests',
                    'action' => 'one'
                ],
                //expected
                true
            ],
            'happy-array' => [
                //permissions
                [[
                    'plugin' => ['Tests'],
                    'role' => ['test'],
                    'controller' => ['Tests'],
                    'action' => ['one', 'two'],
                ]],
                //user
                [
                    'id' => 1,
                    'username' => 'luke',
                    'role' => 'test',
                ],
                //request
                [
                    'plugin' => 'Tests',
                    'controller' => 'Tests',
                    'action' => 'three'
                ],
                //expected
                false
            ],
            'happy-callback-check-params' => [
                //permissions
                [[
                    'plugin' => ['Tests'],
                    'role' => ['test'],
                    'controller' => ['Tests'],
                    'action' => ['one', 'two'],
                    'allowed' => function ($user, $role, $request) {
                        return $user['id'] === 1 && $role = 'test' && $request->plugin == 'Tests';
                    }
                ]],
                //user
                [
                    'id' => 1,
                    'username' => 'luke',
                    'role' => 'test',
                ],
                //request
                [
                    'plugin' => 'Tests',
                    'controller' => 'Tests',
                    'action' => 'one'
                ],
                //expected
                true
            ],
            'happy-callback-deny' => [
                //permissions
                [[
                    'plugin' => ['*'],
                    'role' => ['test'],
                    'controller' => ['Tests'],
                    'action' => ['one', 'two'],
                    'allowed' => function ($user, $role, $request) {
                        return false;
                    }
                ]],
                //user
                [
                    'id' => 1,
                    'username' => 'luke',
                    'role' => 'test',
                ],
                //request
                [
                    'plugin' => 'Tests',
                    'controller' => 'Tests',
                    'action' => 'one'
                ],
                //expected
                false
            ],
            'happy-prefix' => [
                //permissions
                [[
                    'role' => ['test'],
                    'prefix' => ['admin'],
                    'controller' => ['Tests'],
                    'action' => ['one', 'two'],
                ]],
                //user
                [
                    'id' => 1,
                    'username' => 'luke',
                    'role' => 'test',
                ],
                //request
                [
                    'prefix' => 'admin',
                    'controller' => 'Tests',
                    'action' => 'one'
                ],
                //expected
                true
            ],
            'deny-prefix' => [
                //permissions
                [[
                    'role' => ['test'],
                    'prefix' => ['admin'],
                    'controller' => ['Tests'],
                    'action' => ['one', 'two'],
                ]],
                //user
                [
                    'id' => 1,
                    'username' => 'luke',
                    'role' => 'test',
                ],
                //request
                [
                    'controller' => 'Tests',
                    'action' => 'one'
                ],
                //expected
                false
            ],
            'star-prefix' => [
                //permissions
                [[
                    'role' => ['test'],
                    'prefix' => '*',
                    'controller' => ['Tests'],
                    'action' => ['one', 'two'],
                ]],
                //user
                [
                    'id' => 1,
                    'username' => 'luke',
                    'role' => 'test',
                ],
                //request
                [
                    'prefix' => 'admin',
                    'controller' => 'Tests',
                    'action' => 'one'
                ],
                //expected
                true
            ],
            'array-prefix' => [
                //permissions
                [[
                    'role' => ['test'],
                    'prefix' => ['one', 'admin'],
                    'controller' => '*',
                    'action' => '*',
                ]],
                //user
                [
                    'id' => 1,
                    'username' => 'luke',
                    'role' => 'test',
                ],
                //request
                [
                    'prefix' => 'admin',
                    'controller' => 'Tests',
                    'action' => 'one'
                ],
                //expected
                true
            ],
            'array-prefix' => [
                //permissions
                [
                    [
                        'role' => ['test'],
                        'prefix' => ['one', 'admin'],
                        'controller' => '*',
                        'action' => 'one',
                        'allowed' => false,
                    ],
                    [
                        'role' => ['test'],
                        'prefix' => ['one', 'admin'],
                        'controller' => '*',
                        'action' => '*',
                    ],
                ],
                //user
                [
                    'id' => 1,
                    'username' => 'luke',
                    'role' => 'test',
                ],
                //request
                [
                    'prefix' => 'admin',
                    'controller' => 'Tests',
                    'action' => 'one'
                ],
                //expected
                false
            ],
        ];
    }
}

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
namespace CakeDC\Users\Auth\Rules;

use Cake\Http\ServerRequest;
use Cake\Network\Request;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * @property Owner Owner
 * @property ServerRequest request
 */
class OwnerTest extends TestCase
{

    public $fixtures = [
        'plugin.CakeDC/Users.posts',
        'plugin.CakeDC/Users.users',
        'plugin.CakeDC/Users.posts_users',
    ];

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    public function setUp()
    {
        $this->Owner = new Owner();
        $this->request = new ServerRequest();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    public function tearDown()
    {
        unset($this->Owner);
    }

    /**
     * test
     *
     * @return void
     */
    public function testAllowed()
    {
        $this->request = $this->request
            ->withParam('plugin', 'CakeDC/Users')
            ->withParam('controller', 'Posts')
            ->withParam('pass', ['00000000-0000-0000-0000-000000000001']);
        $user = [
            'id' => '00000000-0000-0000-0000-000000000001',
        ];
        $this->assertTrue($this->Owner->allowed($user, 'user', $this->request));
    }

    /**
     * test
     *
     * @return void
     */
    public function testAllowedUsingTableAlias()
    {
        $this->Owner = new Owner([
            'table' => 'Posts'
        ]);
        $this->request = $this->request->withParam('pass', ['00000000-0000-0000-0000-000000000001']);
        $user = [
            'id' => '00000000-0000-0000-0000-000000000001',
        ];
        $this->assertTrue($this->Owner->allowed($user, 'user', $this->request));
    }

    /**
     * test
     *
     * @return void
     */
    public function testAllowedUsingTableInstance()
    {
        $this->Owner = new Owner([
            'table' => TableRegistry::get('CakeDC/Users.Posts'),
        ]);
        $this->request = $this->request->withParam('pass', ['00000000-0000-0000-0000-000000000001']);
        $user = [
            'id' => '00000000-0000-0000-0000-000000000001',
        ];
        $this->assertTrue($this->Owner->allowed($user, 'user', $this->request));
    }

    /**
     * test
     *
     * @return void
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage Missing Table alias, we could not extract a default table from the request
     */
    public function testAllowedShouldThrowExceptionBecauseEmptyAliasFromRequest()
    {
        $this->request = $this->request->withParam('pass', ['00000000-0000-0000-0000-000000000001']);
        $user = [
            'id' => '00000000-0000-0000-0000-000000000001',
        ];
        $this->Owner->allowed($user, 'user', $this->request);
    }

    /**
     * test
     *
     * @return void
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage Missing column column_not_found in table Posts while checking ownership permissions for user 00000000-0000-0000-0000-000000000001
     */
    public function testAllowedShouldThrowExceptionBecauseForeignKeyNotPresentInTable()
    {
        $this->Owner = new Owner([
            'table' => TableRegistry::get('CakeDC/Users.Posts'),
            'ownerForeignKey' => 'column_not_found',
        ]);
        $this->request = $this->request->withParam('pass', ['00000000-0000-0000-0000-000000000001']);
        $user = [
            'id' => '00000000-0000-0000-0000-000000000001',
        ];
        $this->Owner->allowed($user, 'user', $this->request);
    }

    /**
     * test
     *
     * @return void
     */
    public function testNotAllowedBecauseNotOwner()
    {
        $this->request = $this->request->addParams([
            'plugin' => 'CakeDC/Users',
            'controller' => 'Posts',
            'pass' => ['00000000-0000-0000-0000-000000000002']
        ]);
        $user = [
            'id' => '00000000-0000-0000-0000-000000000001',
        ];
        $this->assertFalse($this->Owner->allowed($user, 'user', $this->request));
    }

    /**
     * test
     *
     * @return void
     */
    public function testNotAllowedBecauseUserNotFound()
    {
        $this->request = $this->request->addParams([
            'plugin' => 'CakeDC/Users',
            'controller' => 'Posts',
            'pass' => ['00000000-0000-0000-0000-000000000002']
        ]);
        $user = [
            'id' => '99999999-0000-0000-0000-000000000000',
        ];
        $this->assertFalse($this->Owner->allowed($user, 'user', $this->request));
    }

    /**
     * test
     *
     * @return void
     */
    public function testNotAllowedBecausePostNotFound()
    {
        $this->request = $this->request->addParams([
            'plugin' => 'CakeDC/Users',
            'controller' => 'Posts',
            'pass' => ['99999999-0000-0000-0000-000000000000'] //not found
        ]);
        $user = [
            'id' => '00000000-0000-0000-0000-000000000001',
        ];
        $this->assertFalse($this->Owner->allowed($user, 'user', $this->request));
    }

    /**
     * test
     *
     * @return void
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage Missing column user_id in table NoDefaultTable while checking ownership permissions for user 00000000-0000-0000-0000-000000000001
     */
    public function testNotAllowedBecauseNoDefaultTable()
    {
        $this->request = $this->request->addParams([
            'plugin' => 'CakeDC/Users',
            'controller' => 'NoDefaultTable',
            'pass' => ['00000000-0000-0000-0000-000000000001']
        ]);
        $user = [
            'id' => '00000000-0000-0000-0000-000000000001',
        ];
        $this->assertFalse($this->Owner->allowed($user, 'user', $this->request));
    }

    /**
     * Test using the Owner rule in a belongsToMany association
     * Posts belongsToMany Users
     * @return void
     */
    public function testAllowedBelongsToMany()
    {
        $this->Owner = new Owner([
            'table' => 'PostsUsers',
            'id' => 'post_id',
        ]);
        $this->request = $this->request->addParams([
            'plugin' => 'CakeDC/Users',
            'controller' => 'IsNotUsed',
            'pass' => ['00000000-0000-0000-0000-000000000001']
        ]);
        $user = [
            'id' => '00000000-0000-0000-0000-000000000001',
        ];
        $this->assertTrue($this->Owner->allowed($user, 'user', $this->request));
    }

    /**
     * Test using the Owner rule in a belongsToMany association
     * Posts belongsToMany Users
     * @return void
     */
    public function testNotAllowedBelongsToMany()
    {
        $this->Owner = new Owner([
            'table' => 'PostsUsers',
            'id' => 'post_id',
        ]);
        $this->request = $this->request->addParams([
            'plugin' => 'CakeDC/Users',
            'controller' => 'IsNotUsed',
            'pass' => ['00000000-0000-0000-0000-000000000002']
        ]);
        $user = [
            'id' => '00000000-0000-0000-0000-000000000001',
        ];
        $this->assertFalse($this->Owner->allowed($user, 'user', $this->request));
    }
}

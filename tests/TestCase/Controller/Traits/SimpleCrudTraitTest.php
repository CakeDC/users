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

use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Datasource\Paging\PaginatedResultSet;
use Cake\Datasource\ResultSetDecorator;
use Cake\Http\Response;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

/**
 * Class SimpleCrudTraitTest
 *
 * @package CakeDC\Users\Test\TestCase\Controller\Traits
 * @property \CakeDC\Users\Controller\Traits\SimpleCrudTrait&\PHPUnit\Framework\MockObject\MockObject $Trait
 */
#[AllowMockObjectsWithoutExpectations]
class SimpleCrudTraitTest extends BaseTrait
{
    public $viewVars;

    /**
     * setup
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->traitClassName = 'CakeDC\Users\Controller\UsersController';
        $this->traitMockMethods = ['dispatchEvent', 'redirect', 'getUsersTable', 'set', 'fetchTable', 'paginate'];
        $this->traitMockAddMethods = ['isStopped'];
        parent::setUp();
        $viewVarsContainer = $this;
        $this->Trait->method('set')
            ->willReturnCallback(function ($param1, $param2 = null) use ($viewVarsContainer) {
                $viewVarsContainer->viewVars[$param1] = $param2;

                return $viewVarsContainer->Trait;
            });
        $this->Trait->expects($this->once())
            ->method('fetchTable')
            ->willReturn($this->table);
    }

    /**
     * tearDown
     *
     * @return void
     */
    public function tearDown(): void
    {
        $this->viewVars = null;
        parent::tearDown();
    }

    /**
     * test
     *
     * @return void
     */
    public function testIndex(): void
    {
        $results = new ResultSetDecorator([]);
        $paginated = new PaginatedResultSet($results, []);
        $this->Trait->expects($this->once())
            ->method('paginate')
            ->with($this->table)
            ->willReturn($paginated);
        $this->Trait->index();
        $expected = [
            'Users' => $paginated,
            'tableAlias' => 'Users',
        ];
        $this->assertEquals($expected, $this->viewVars);
    }

    /**
     * test
     *
     * @return void
     */
    public function testView(): void
    {
        $id = '00000000-0000-0000-0000-000000000001';
        $this->Trait->view($id);
        $expected = [
            'Users' => $this->table->get($id),
            'tableAlias' => 'Users',
        ];
        $this->assertEquals($expected, $this->viewVars);
    }

    /**
     * test
     *
     * @return void
     */
    public function testViewNotFound(): void
    {
        $this->expectException(RecordNotFoundException::class);
        $this->Trait->view('00000000-0000-0000-0000-000000000000');
    }

    /**
     * test
     *
     * @return void
     */
    public function testViewInvalidPK(): void
    {
        $this->expectException(InvalidPrimaryKeyException::class);
        $this->Trait->view();
    }

    /**
     * test
     *
     * @return void
     */
    public function testAddGet(): void
    {
        $this->mockRequestGet();
        $this->Trait->add();
        $expected = [
            'Users' => $this->table->newEmptyEntity(),
            'tableAlias' => 'Users',
        ];
        $this->assertEquals($expected, $this->viewVars);
    }

    /**
     * test
     *
     * @return void
     */
    public function testAddPostHappy(): void
    {
        $this->assertSame(0, $this->table->find()->where(['username' => 'testuser'])->count());
        $this->mockRequestPost();
        $flash = $this->mockFlash();
        $this->Trait->getRequest()->expects($this->once())
            ->method('getData')
            ->with()
            ->willReturn([
                'username' => 'testuser',
                'email' => 'testuser@test.com',
                'password' => 'password',
                'first_name' => 'test',
                'last_name' => 'user',
            ]);
        $flash->expects('set')
            ->with('The User has been saved', ['element' => 'success']);

        $this->Trait->add();

        $this->assertSame(1, $this->table->find()->where(['username' => 'testuser'])->count());
    }

    /**
     * test
     *
     * @return void
     */
    public function testAddPostErrors(): void
    {
        $this->assertSame(0, $this->table->find()->where(['username' => 'testuser'])->count());
        $this->mockRequestPost();
        $flash = $this->mockFlash();
        $this->Trait->getRequest()->expects($this->once())
            ->method('getData')
            ->with()
            ->willReturn([
                'username' => 'testuser',
                'email' => 'testuser@test.com',
                'first_name' => 'test',
                'last_name' => 'user',
            ]);

        $flash->expects('set')
            ->with('The User could not be saved', ['element' => 'error']);

        $this->Trait->add();

        $this->assertSame(0, $this->table->find()->where(['username' => 'testuser'])->count());
    }

    /**
     * test
     *
     * @return void
     */
    public function testEditPostHappy(): void
    {
        $this->assertEquals('user-1@test.com', $this->table->get('00000000-0000-0000-0000-000000000001')->email);
        $this->mockRequestPost(['patch', 'post', 'put']);
        $flash = $this->mockFlash();
        $this->Trait->getRequest()->expects($this->once())
            ->method('getData')
            ->with()
            ->willReturn([
                'email' => 'newtestuser@test.com',
            ]);

        $flash->expects('set')
            ->with('The User has been saved', ['element' => 'success']);

        $this->Trait->edit('00000000-0000-0000-0000-000000000001');

        $this->assertEquals('newtestuser@test.com', $this->table->get('00000000-0000-0000-0000-000000000001')->email);
    }

    /**
     * test
     *
     * @return void
     */
    public function testEditPostErrors(): void
    {
        $this->assertEquals('user-1@test.com', $this->table->get('00000000-0000-0000-0000-000000000001')->email);
        $this->mockRequestPost(['patch', 'post', 'put']);
        $flash = $this->mockFlash();
        $this->Trait->getRequest()->expects($this->once())
            ->method('getData')
            ->with()
            ->willReturn([
                'email' => 'not-an-email',
            ]);

        $flash->expects('set')
            ->with('The User could not be saved', ['element' => 'error']);

        $this->Trait->edit('00000000-0000-0000-0000-000000000001');

        $this->assertEquals('user-1@test.com', $this->table->get('00000000-0000-0000-0000-000000000001')->email);
    }

    /**
     * test
     *
     * @return void
     */
    public function testDeleteHappy(): void
    {
        $this->expectException(RecordNotFoundException::class);
        $this->assertNotEmpty($this->table->get('00000000-0000-0000-0000-000000000001'));
        $request = $this->getMockBuilder('Cake\Http\ServerRequest')
            ->onlyMethods(['is', 'allowMethod'])
            ->getMock();
        $request->expects($this->once())
            ->method('allowMethod')
            ->with(['post', 'delete']);
        $this->Trait->setRequest($request);

        $flash = $this->mockFlash();
        $flash->expects('set')
            ->with('The User has been deleted', ['element' => 'success']);
        $this->Trait->expects($this->once())
            ->method('redirect')
            ->willReturn(new Response());

        $this->Trait->delete('00000000-0000-0000-0000-000000000001');

        $this->table->get('00000000-0000-0000-0000-000000000001');
    }

    /**
     * test
     *
     * @return void
     */
    public function testDeleteNotFound(): void
    {
        $this->expectException(RecordNotFoundException::class);
        $this->assertNotEmpty($this->table->get('00000000-0000-0000-0000-000000000001'));

        $request = $this->getMockBuilder('Cake\Http\ServerRequest')
            ->onlyMethods(['is', 'allowMethod'])
            ->getMock();
        $request->expects($this->once())
            ->method('allowMethod')
            ->with(['post', 'delete']);
        $this->Trait->setRequest($request);

        $this->Trait->delete('00000000-0000-0000-0000-000000000000');
    }
}

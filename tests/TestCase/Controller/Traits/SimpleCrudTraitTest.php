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

use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;

/**
 * Class SimpleCrudTraitTest
 *
 * @package CakeDC\Users\Test\TestCase\Controller\Traits
 */
class SimpleCrudTraitTest extends BaseTraitTest
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
        $this->traitMockMethods = ['dispatchEvent', 'isStopped', 'redirect', 'getUsersTable', 'set', 'loadModel', 'paginate'];
        parent::setUp();
        $viewVarsContainer = $this;
        $this->Trait->expects($this->any())
            ->method('set')
            ->will($this->returnCallback(function ($param1, $param2 = null) use ($viewVarsContainer) {
                $viewVarsContainer->viewVars[$param1] = $param2;
            }));
        $this->Trait->expects($this->once())
            ->method('loadModel')
            ->will($this->returnValue($this->table));
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
    public function testIndex()
    {
        $this->Trait->expects($this->once())
            ->method('paginate')
            ->with($this->table)
            ->will($this->returnValue([]));
        $this->Trait->index();
        $expected = [
            'Users' => [],
            'tableAlias' => 'Users',
            '_serialize' => [
                'Users',
                'tableAlias',
            ],
        ];
        $this->assertSame($expected, $this->viewVars);
    }

    /**
     * test
     *
     * @return void
     */
    public function testView()
    {
        $id = '00000000-0000-0000-0000-000000000001';
        $this->Trait->view($id);
        $expected = [
            'Users' => $this->table->get($id),
            'tableAlias' => 'Users',
            '_serialize' => [
                'Users',
                'tableAlias',
            ],
        ];
        $this->assertEquals($expected, $this->viewVars);
    }

    /**
     * test
     *
     * @return void
     */
    public function testViewNotFound()
    {
        $this->expectException(RecordNotFoundException::class);
        $this->Trait->view('00000000-0000-0000-0000-000000000000');
    }

    /**
     * test
     *
     * @return void
     */
    public function testViewInvalidPK()
    {
        $this->expectException(InvalidPrimaryKeyException::class);
        $this->Trait->view();
    }

    /**
     * test
     *
     * @return void
     */
    public function testAddGet()
    {
        $this->_mockRequestGet();
        $this->Trait->add();
        $expected = [
            'Users' => $this->table->newEmptyEntity(),
            'tableAlias' => 'Users',
            '_serialize' => [
                'Users',
                'tableAlias',
            ],
        ];
        $this->assertEquals($expected, $this->viewVars);
    }

    /**
     * test
     *
     * @return void
     */
    public function testAddPostHappy()
    {
        $this->assertSame(0, $this->table->find()->where(['username' => 'testuser'])->count());
        $this->_mockRequestPost();
        $this->_mockFlash();
        $this->Trait->getRequest()->expects($this->at(0))
            ->method('is')
            ->with('post')
            ->will($this->returnValue(true));
        $this->Trait->getRequest()->expects($this->at(1))
            ->method('getData')
            ->with()
            ->will($this->returnValue([
                'username' => 'testuser',
                'email' => 'testuser@test.com',
                'password' => 'password',
                'first_name' => 'test',
                'last_name' => 'user',
            ]));
        $this->Trait->Flash->expects($this->once())
            ->method('success')
            ->with('The User has been saved');

        $this->Trait->add();

        $this->assertSame(1, $this->table->find()->where(['username' => 'testuser'])->count());
    }

    /**
     * test
     *
     * @return void
     */
    public function testAddPostErrors()
    {
        $this->assertSame(0, $this->table->find()->where(['username' => 'testuser'])->count());
        $this->_mockRequestPost();
        $this->_mockFlash();
        $this->Trait->getRequest()->expects($this->at(0))
            ->method('is')
            ->with('post')
            ->will($this->returnValue(true));
        $this->Trait->getRequest()->expects($this->at(1))
            ->method('getData')
            ->with()
            ->will($this->returnValue([
                'username' => 'testuser',
                'email' => 'testuser@test.com',
                'first_name' => 'test',
                'last_name' => 'user',
            ]));

        $this->Trait->Flash->expects($this->once())
            ->method('error')
            ->with('The User could not be saved');

        $this->Trait->add();

        $this->assertSame(0, $this->table->find()->where(['username' => 'testuser'])->count());
    }

    /**
     * test
     *
     * @return void
     */
    public function testEditPostHappy()
    {
        $this->assertEquals('user-1@test.com', $this->table->get('00000000-0000-0000-0000-000000000001')->email);
        $this->_mockRequestPost(['patch', 'post', 'put']);
        $this->_mockFlash();
        $this->Trait->getRequest()->expects($this->at(0))
            ->method('is')
            ->with(['patch', 'post', 'put'])
            ->will($this->returnValue(true));
        $this->Trait->getRequest()->expects($this->at(1))
            ->method('getData')
            ->with()
            ->will($this->returnValue([
                'email' => 'newtestuser@test.com',
            ]));

        $this->Trait->Flash->expects($this->once())
            ->method('success')
            ->with('The User has been saved');

        $this->Trait->edit('00000000-0000-0000-0000-000000000001');

        $this->assertEquals('newtestuser@test.com', $this->table->get('00000000-0000-0000-0000-000000000001')->email);
    }

    /**
     * test
     *
     * @return void
     */
    public function testEditPostErrors()
    {
        $this->assertEquals('user-1@test.com', $this->table->get('00000000-0000-0000-0000-000000000001')->email);
        $this->_mockRequestPost(['patch', 'post', 'put']);
        $this->_mockFlash();
        $this->Trait->getRequest()->expects($this->at(0))
            ->method('is')
            ->with(['patch', 'post', 'put'])
            ->will($this->returnValue(true));
        $this->Trait->getRequest()->expects($this->at(1))
            ->method('getData')
            ->with()
            ->will($this->returnValue([
                'email' => 'not-an-email',
            ]));

        $this->Trait->Flash->expects($this->once())
            ->method('error')
            ->with('The User could not be saved');

        $this->Trait->edit('00000000-0000-0000-0000-000000000001');

        $this->assertEquals('user-1@test.com', $this->table->get('00000000-0000-0000-0000-000000000001')->email);
    }

    /**
     * test
     *
     * @return void
     */
    public function testDeleteHappy()
    {
        $this->expectException(RecordNotFoundException::class);
        $this->assertNotEmpty($this->table->get('00000000-0000-0000-0000-000000000001'));
        $this->_mockRequestPost();
        $request = $this->getMockBuilder('Cake\Http\ServerRequest')
            ->setMethods(['is', 'allowMethod'])
            ->getMock();
        $request->expects($this->any())
            ->method('allowMethod')
            ->with(['post', 'delete'])
            ->will($this->returnValue(true));
        $this->Trait->setRequest($request);

        $this->_mockFlash();
        $this->Trait->Flash->expects($this->once())
            ->method('success')
            ->with('The User has been deleted');

        $this->Trait->delete('00000000-0000-0000-0000-000000000001');

        $this->table->get('00000000-0000-0000-0000-000000000001');
    }

    /**
     * test
     *
     * @return void
     */
    public function testDeleteNotFound()
    {
        $this->expectException(RecordNotFoundException::class);
        $this->assertNotEmpty($this->table->get('00000000-0000-0000-0000-000000000001'));
        $this->_mockRequestPost();

        $request = $this->getMockBuilder('Cake\Http\ServerRequest')
            ->setMethods(['is', 'allowMethod'])
            ->getMock();
        $request->expects($this->any())
            ->method('allowMethod')
            ->with(['post', 'delete'])
            ->will($this->returnValue(true));
        $this->Trait->setRequest($request);

        $this->Trait->delete('00000000-0000-0000-0000-000000000000');
    }
}

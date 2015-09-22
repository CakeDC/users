<?php
/**
 * Copyright 2010 - 2015, Cake Development Corporation (+1 702 425 5085) (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2015, Cake Development Corporation (+1 702 425 5085) (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Test\TestCase\Controller\Traits;

use CakeDC\Users\Test\TestCase\Controller\Traits\BaseTraitTest;
use Cake\Network\Request;

class SimpleCrudTraitTest extends BaseTraitTest
{
    public $viewVars;

    /**
     * setup
     *
     * @return void
     */
    public function setUp()
    {
        $this->traitClassName = 'CakeDC\Users\Controller\Traits\SimpleCrudTrait';
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
    public function tearDown()
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
                'tableAlias'
            ]
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
                'tableAlias'
            ]
        ];
        $this->assertEquals($expected, $this->viewVars);
    }

    /**
     * test
     *
     * @return void
     * @expectedException Cake\Datasource\Exception\RecordNotFoundException
     */
    public function testViewNotFound()
    {
        $this->Trait->view('00000000-0000-0000-0000-000000000000');
    }

    /**
     * test
     *
     * @return void
     * @expectedException Cake\Datasource\Exception\InvalidPrimaryKeyException
     */
    public function testViewInvalidPK()
    {
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
            'Users' => $this->table->newEntity(),
            'tableAlias' => 'Users',
            '_serialize' => [
                'Users',
                'tableAlias'
            ]
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
        $this->Trait->request->data = [
            'username' => 'testuser',
            'email' => 'testuser@test.com',
            'password' => 'password',
            'first_name' => 'test',
            'last_name' => 'user',
        ];

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
        $this->Trait->request->data = [
            'username' => 'testuser',
            'email' => 'testuser@test.com',
            'first_name' => 'test',
            'last_name' => 'user',
        ];

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
        $this->Trait->request->data = [
            'email' => 'newtestuser@test.com',
        ];

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
        $this->Trait->request->data = [
            'email' => 'not-an-email',
        ];

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
     * @expectedException Cake\Datasource\Exception\RecordNotFoundException
     */
    public function testDeleteHappy()
    {
        $this->assertNotEmpty($this->table->get('00000000-0000-0000-0000-000000000001'));
        $this->_mockRequestPost();
        $this->Trait->request->expects($this->any())
            ->method('allow')
            ->with(['post', 'delete'])
            ->will($this->returnValue(true));

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
     * @expectedException Cake\Datasource\Exception\RecordNotFoundException
     */
    public function testDeleteNotFound()
    {
        $this->assertNotEmpty($this->table->get('00000000-0000-0000-0000-000000000001'));
        $this->_mockRequestPost();
        $this->Trait->request->expects($this->any())
            ->method('allow')
            ->with(['post', 'delete'])
            ->will($this->returnValue(true));

        $this->Trait->delete('00000000-0000-0000-0000-000000000000');
    }
}

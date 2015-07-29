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

namespace Users\Controller\Traits;

use Cake\Network\Exception\NotFoundException;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;

/**
 * Covers the baked CRUD actions, note we could use Crud Plugin too
 *
 */
trait SimpleCrudTrait
{
    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $table = $this->loadModel();
        $tableAlias = $table->alias();
        $this->set($tableAlias, $this->paginate($table));
        $this->set('tableAlias', $tableAlias);
        $this->set('_serialize', [$tableAlias, 'tableAlias']);
    }

    /**
     * View method
     *
     * @param string|null $id User id.
     * @return void
     * @throws NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $table = $this->loadModel();
        $tableAlias = $table->alias();
        $entity = $table->get($id, [
            'contain' => []
        ]);
        $this->set($tableAlias, $entity);
        $this->set('tableAlias', $tableAlias);
        $this->set('_serialize', [$tableAlias, 'tableAlias']);
    }

    /**
     * Add method
     *
     * @return void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $table = $this->loadModel();
        $tableAlias = $table->alias();
        $entity = $table->newEntity();
        $this->set($tableAlias, $entity);
        $this->set('tableAlias', $tableAlias);
        $this->set('_serialize', [$tableAlias, 'tableAlias']);
        if (!$this->request->is('post')) {
            return;
        }
        $entity = $table->patchEntity($entity, $this->request->data);
        if ($table->save($entity)) {
            $this->Flash->success(__d('Users', 'The {0} has been saved', Inflector::humanize($tableAlias)));
            return $this->redirect(['action' => 'index']);
        }
        $this->Flash->error(__d('Users', 'The {0} could not be saved', Inflector::humanize($tableAlias)));
    }

    /**
     * Edit method
     *
     * @param string|null $id User id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $table = $this->loadModel();
        $tableAlias = $table->alias();
        $entity = $table->get($id, [
            'contain' => []
        ]);
        $this->set($tableAlias, $entity);
        $this->set('tableAlias', $tableAlias);
        $this->set('_serialize', [$tableAlias, 'tableAlias']);
        if (!$this->request->is(['patch', 'post', 'put'])) {
            return;
        }
        $entity = $table->patchEntity($entity, $this->request->data);
        if ($table->save($entity)) {
            $this->Flash->success(__d('Users', 'The {0} has been saved', Inflector::humanize($tableAlias)));
            return $this->redirect(['action' => 'index']);
        }
        $this->Flash->error(__d('Users', 'The {0} could not be saved', Inflector::humanize($tableAlias)));
    }

    /**
     * Delete method
     *
     * @param string|null $id User id.
     * @return void Redirects to index.
     * @throws NotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $table = $this->loadModel();
        $tableAlias = $table->alias();
        $entity = $table->get($id, [
            'contain' => []
        ]);
        if ($table->delete($entity)) {
            $this->Flash->success(__d('Users', 'The {0} has been deleted', Inflector::humanize($tableAlias)));
        } else {
            $this->Flash->error(__d('Users', 'The {0} could not be deleted', Inflector::humanize($tableAlias)));
        }
        return $this->redirect(['action' => 'index']);
    }
}

<?php
class_alias('CakeDC\Users\Test\App\Controller\AppController', 'App\Controller\AppController');

// Ensure default test connection is defined
if (!getenv('db_dsn')) {
    putenv('db_dsn=sqlite:///:memory:');
}

Cake\Datasource\ConnectionManager::setConfig('test', [
    'url' => getenv('db_dsn'),
//    'className' => 'Cake\Database\Connection',
//    'driver' => 'Cake\Database\Driver\Postgres',
//    'persistent' => true,
//    'host' => 'localhost',
//    'username' => 'my_app',
//    'password' => null,
//    'database' => 'test',
//    'schema' => 'public',
//    'port' => 5432,
//    'encoding' => 'utf8',
//    'flags' => [],
//    'init' => [],
    'timezone' => 'UTC'
]);

\Cake\Core\Configure::write('App.paths.templates', [
    APP . 'Template/',
]);

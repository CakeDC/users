<?php
/**
 * Test suite bootstrap.
 *
 * This function is used to find the location of CakePHP whether CakePHP
 * has been installed as a dependency of the plugin, or the plugin is itself
 * installed as a dependency of an application.
 */

use Cake\Core\Configure;
use Cake\Routing\DispatcherFactory;
use Cake\Routing\Filter\ControllerFactory;

$findRoot = function ($root) {
    do {
        $lastRoot = $root;
        $root = dirname($root);
        if (is_dir($root . '/vendor/cakephp/cakephp')) {
            return $root;
        }
    } while ($root !== $lastRoot);
    throw new Exception("Cannot find the root of the application, unable to run tests");
};
$root = $findRoot(__FILE__);
unset($findRoot);
chdir($root);

define('CONFIG', $root . '/tests/config/');

require $root . '/vendor/cakephp/cakephp/tests/bootstrap.php';
\Cake\Core\Plugin::load('CakeDC/Users', [
        'path' => dirname(dirname(__FILE__)) . DS,
    ]);


if (file_exists($root . '/config/bootstrap.php')) {
    require $root . '/config/bootstrap.php';
}

/**
 * Connect middleware/dispatcher filters.
 */
DispatcherFactory::add('Routing');
DispatcherFactory::add('ControllerFactory');


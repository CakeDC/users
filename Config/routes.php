<?php
Router::connect('/users', array('plugin' => 'Users', 'controller' => 'users'));
Router::connect('/users/index/*', array('plugin' => 'Users', 'controller' => 'users'));
Router::connect('/users/:action/*', array('plugin' => 'Users', 'controller' => 'users'));
Router::connect('/users/users/:action/*', array('plugin' => 'Users', 'controller' => 'users'));
Router::connect('/login', array('plugin' => 'Users', 'controller' => 'users', 'action' => 'login'));
Router::connect('/logout', array('plugin' => 'Users', 'controller' => 'users', 'action' => 'logout'));
Router::connect('/register', array('plugin' => 'Users', 'controller' => 'users', 'action' => 'add'));
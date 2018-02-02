<?php
Router::connect('/users', ['plugin' => 'users', 'controller' => 'users']);
Router::connect('/users/index/*', ['plugin' => 'users', 'controller' => 'users']);
Router::connect('/users/:action/*', ['plugin' => 'users', 'controller' => 'users']);
Router::connect('/users/users/:action/*', ['plugin' => 'users', 'controller' => 'users']);
Router::connect('/login', ['plugin' => 'users', 'controller' => 'users', 'action' => 'login']);
Router::connect('/logout', ['plugin' => 'users', 'controller' => 'users', 'action' => 'logout']);
Router::connect('/register', ['plugin' => 'users', 'controller' => 'users', 'action' => 'add']);
<?php
/**
 * Copyright 2010 - 2018, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2017, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
namespace CakeDC\Users\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\Configure;

class SetupComponent extends Component
{
    /**
     * @param array $config component configuration
     * @throws \Exception
     */
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->getController()->loadComponent('CakeDC/Users.UsersAuth');
        list($plugin, $controller) = pluginSplit(Configure::read('Users.controller'));
        if ($this->getController()->getRequest()->getParam('plugin', null) === $plugin &&
            $this->getController()->getRequest()->getParam('controller') === $controller
        ) {
            $this->getController()->Auth->allow(['login']);
        }
    }
}

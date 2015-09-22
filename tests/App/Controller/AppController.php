<?php
namespace CakeDC\Users\Test\App\Controller;

use Cake\Controller\Controller;

/**
 * This is a placeholder class.
 * Create the same file in app/Controller/AppController.php
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 */
class AppController extends Controller
{

    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('Flash');
        // $this->loadComponent('CakeDC/Users.UsersAuth');
        $this->loadComponent('RequestHandler');
    }
}

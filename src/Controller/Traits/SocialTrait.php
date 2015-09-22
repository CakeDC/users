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

namespace CakeDC\Users\Controller\Traits;

use CakeDC\Users\Auth\Factory\OpauthFactory;
use Cake\Core\Configure;
use Cake\Network\Exception\NotFoundException;
use Cake\Routing\Router;

/**
 * Covers registration features and email token validation
 *
 */
trait SocialTrait
{

    /**
     * Start Opauth authentication
     *
     * @param bool|false $callback callback
     * @return void
     */
    public function opauthInit($callback = null)
    {
        $this->autoRender = false;
        $Opauth = $this->_getOpauthInstance();
        $response = $Opauth->run();
        if (empty($callback)) {
            return;
        }
        $url = $this->_generateOpauthCompleteUrl();
        $this->request->session()->write(Configure::read('Users.Key.Session.social'), $response);
        return $this->redirect($url);
    }

    /**
     * Generates the opauth callback url
     *
     * @return string Full translated URL with base path.
     */
    protected function _generateOpauthCompleteUrl()
    {
        $url = Configure::read('Opauth.complete_url');
        if (!is_array($url)) {
            $url = Router::parse($url);
        }
        $url['?'] = ['social' => $this->request->query('code')];
        return Router::url($url, true);
    }

    /**
     * Render the social email form
     *
     * @throws NotFoundException
     * @return void
     */
    public function socialEmail()
    {
        if (!$this->request->session()->check(Configure::read('Users.Key.Session.social'))) {
            throw new NotFoundException();
        }
    }

    /**
     * Gets OpauthFactory instance
     *
     * @return OpauthFactory
     */
    protected function _getOpauthInstance()
    {
        return OpauthFactory::create(Configure::read('Opauth'));
    }
}

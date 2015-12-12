<?php
/**
 * Copyright 2010 - 2014, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2014, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('SessionComponent', 'Controller/Component');
App::uses('Folder', 'Utility');

/**
 * FlashMessage Component
 *
 * Check if exists FlashComponent
 *
 * @property SessionComponent $Session
 * @property Folder $Folder
 */
class FlashMessageComponent extends SessionComponent {

    /**
     * Check if FlashComponent file exists for the current CakePHP version
     *
     * @return bool
     */
    protected function _checkFlashComponentExists() {
        $dir = new Folder(APP. 'Vendor/cakephp/cakephp/lib/Cake/Controller/Component/');
        $files = $dir->find('.*\.php');
        return array_key_exists('FlashComponent.php', $files);
    }

    /**
     * Returns the component according to the version of CakePHP
     *
     * @param string $msg Flash message
     * @return object
     */
    public function getComponentByVersion($msg) {
        if ($this->_checkFlashComponentExists() === true) {
            $this->Flash = $this->Components->load('Flash');
            return $this->Flash->set($msg);
        } else {
            return $this->setFlash($msg);
        }
    }

    /**
     * Returns which helper will be used.
     *
     * @returns string
     */
    public function getFlashMessageHelper() {
        if ($this->_checkFlashComponentExists() === true) {
            return 'Flash';
        } else {
            return 'Session';
        }
    }
}

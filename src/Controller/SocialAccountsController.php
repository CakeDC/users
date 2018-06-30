<?php
/**
 * Copyright 2010 - 2017, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2017, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Controller;

use Cake\Core\Configure;
use CakeDC\Users\Controller\AppController;
use CakeDC\Users\Exception\AccountAlreadyActiveException;
use CakeDC\Users\Model\Table\SocialAccountsTable;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Response;

/**
 * SocialAccounts Controller
 *
 * @property SocialAccountsTable $SocialAccounts
 */
class SocialAccountsController extends AppController
{

    /**
     * Init
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();
        $this->Auth->allow(['validateAccount', 'resendValidation']);
    }

    /**
     * Validates social account
     *
     * @param string $provider provider
     * @param string $reference reference
     * @param string $token token
     * @return Response
     */
    public function validateAccount($provider, $reference, $token)
    {
        try {
            $result = $this->SocialAccounts->validateAccount($provider, $reference, $token);
            if ($result) {
                $this->Flash->success(__d('CakeDC/Users', Configure::read('Messages.socialAccounts.accountValidated')));
            } else {
                $this->Flash->error(__d('CakeDC/Users', Configure::read('Messages.socialAccounts.failValidate')));
            }
        } catch (RecordNotFoundException $exception) {
            $this->Flash->error(__d('CakeDC/Users', Configure::read('Messages.socialAccounts.invalidToken')));
        } catch (AccountAlreadyActiveException $exception) {
            $this->Flash->error(__d('CakeDC/Users', Configure::read('Messages.socialAccounts.socialAlreadyActive')));
        } catch (\Exception $exception) {
            $this->Flash->error(__d('CakeDC/Users', Configure::read('Messages.socialAccounts.failSocialValidate')));
        }

        return $this->redirect(['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'login']);
    }

    /**
     * Resends validation email if required
     *
     * @param string $provider provider
     * @param string $reference reference
     * @return mixed
     * @throws AccountAlreadyActiveException
     */
    public function resendValidation($provider, $reference)
    {
        try {
            $result = $this->SocialAccounts->resendValidation($provider, $reference);
            if ($result) {
                $this->Flash->success(__d('CakeDC/Users', Configure::read('Messages.socialAccounts.emailSent')));
            } else {
                $this->Flash->error(__d('CakeDC/Users', Configure::read('Messages.socialAccounts.failEmailSend')));
            }
        } catch (RecordNotFoundException $exception) {
            $this->Flash->error(__d('CakeDC/Users', Configure::read('Messages.socialAccounts.invalidAccount')));
        } catch (AccountAlreadyActiveException $exception) {
            $this->Flash->error(__d('CakeDC/Users', Configure::read('Messages.socialAccounts.socialAlreadyActive')));
        } catch (\Exception $exception) {
            $this->Flash->error(__d('CakeDC/Users', Configure::read('Messages.socialAccounts.failEmailResend')));
        }

        return $this->redirect(['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'login']);
    }
}

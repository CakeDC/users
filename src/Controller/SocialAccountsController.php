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

use CakeDC\Users\Controller\AppController;
use CakeDC\Users\Exception\AccountAlreadyActiveException;
use CakeDC\Users\Model\Table\SocialAccountsTable;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Network\Response;

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
                $this->Flash->success(__d('CakeDC/Users', 'Account validated successfully'));
            } else {
                $this->Flash->error(__d('CakeDC/Users', 'Account could not be validated'));
            }
        } catch (RecordNotFoundException $exception) {
            $this->Flash->error(__d('CakeDC/Users', 'Invalid token and/or social account'));
        } catch (AccountAlreadyActiveException $exception) {
            $this->Flash->error(__d('CakeDC/Users', 'Social Account already active'));
        } catch (\Exception $exception) {
            $this->Flash->error(__d('CakeDC/Users', 'Social Account could not be validated'));
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
                $this->Flash->success(__d('CakeDC/Users', 'Email sent successfully'));
            } else {
                $this->Flash->error(__d('CakeDC/Users', 'Email could not be sent'));
            }
        } catch (RecordNotFoundException $exception) {
            $this->Flash->error(__d('CakeDC/Users', 'Invalid account'));
        } catch (AccountAlreadyActiveException $exception) {
            $this->Flash->error(__d('CakeDC/Users', 'Social Account already active'));
        } catch (\Exception $exception) {
            $this->Flash->error(__d('CakeDC/Users', 'Email could not be resent'));
        }

        return $this->redirect(['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'login']);
    }
}

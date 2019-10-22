<?php
declare(strict_types=1);

/**
 * Copyright 2010 - 2019, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2018, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Controller;

use Cake\Datasource\Exception\RecordNotFoundException;
use CakeDC\Users\Exception\AccountAlreadyActiveException;
use CakeDC\Users\Utility\UsersUrl;

/**
 * SocialAccounts Controller
 *
 * @property \CakeDC\Users\Model\Table\SocialAccountsTable $SocialAccounts
 */
class SocialAccountsController extends AppController
{
    /**
     * Init
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
    }

    /**
     * Validates social account
     *
     * @param string $provider provider
     * @param string $reference reference
     * @param string $token token
     * @return \Cake\Http\Response
     */
    public function validateAccount($provider, $reference, $token)
    {
        try {
            $result = $this->SocialAccounts->validateAccount($provider, $reference, $token);
            if ($result) {
                $this->Flash->success(__d('cake_d_c/users', 'Account validated successfully'));
            } else {
                $this->Flash->error(__d('cake_d_c/users', 'Account could not be validated'));
            }
        } catch (RecordNotFoundException $exception) {
            $this->Flash->error(__d('cake_d_c/users', 'Invalid token and/or social account'));
        } catch (AccountAlreadyActiveException $exception) {
            $this->Flash->error(__d('cake_d_c/users', 'Social Account already active'));
        } catch (\Exception $exception) {
            $this->Flash->error(__d('cake_d_c/users', 'Social Account could not be validated'));
        }

        return $this->redirect(UsersUrl::actionUrl('login'));
    }

    /**
     * Resends validation email if required
     *
     * @param string $provider provider
     * @param string $reference reference
     * @return mixed
     * @throws \CakeDC\Users\Exception\AccountAlreadyActiveException
     */
    public function resendValidation($provider, $reference)
    {
        try {
            $result = $this->SocialAccounts->resendValidation($provider, $reference);
            if ($result) {
                $this->Flash->success(__d('cake_d_c/users', 'Email sent successfully'));
            } else {
                $this->Flash->error(__d('cake_d_c/users', 'Email could not be sent'));
            }
        } catch (RecordNotFoundException $exception) {
            $this->Flash->error(__d('cake_d_c/users', 'Invalid account'));
        } catch (AccountAlreadyActiveException $exception) {
            $this->Flash->error(__d('cake_d_c/users', 'Social Account already active'));
        } catch (\Exception $exception) {
            $this->Flash->error(__d('cake_d_c/users', 'Email could not be resent'));
        }

        return $this->redirect(UsersUrl::actionUrl('login'));
    }
}

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

namespace CakeDC\Users\Controller\Traits;

use CakeDC\Auth\Social\MapUser;
use CakeDC\Auth\Social\Service\ServiceFactory;
use CakeDC\Users\Plugin;

/**
 * Actions to allow user to link social accounts
 */
trait LinkSocialTrait
{
    /**
     *  Init link and auth process against provider
     *
     * @param string $alias of the provider.
     * @throws \Cake\Http\Exception\NotFoundException Quando o provider informado não existe
     * @return  \Cake\Http\Response Redirects on successful
     */
    public function linkSocial($alias = null)
    {
        $authUrl = (new ServiceFactory())
                ->setRedirectUriField('callbackLinkSocialUri')
                ->createFromProvider($alias)
                ->getAuthorizationUrl($this->getRequest());

        $this->dispatchEvent(Plugin::EVENT_BEFORE_SOCIAL_LOGIN_REDIRECT, [
            'location' => $authUrl,
            'request' => $this->request,
        ]);

        return $this->redirect($authUrl);
    }

    /**
     * Callback to get user information from provider
     *
     * @param string $alias of the provider.
     * @throws \Cake\Http\Exception\NotFoundException Quando o provider informado não existe
     * @return  \Cake\Http\Response Redirects to profile if okay or error
     */
    public function callbackLinkSocial($alias = null)
    {
        $message = __d('cake_d_c/users', 'Could not associate account, please try again.');
        try {
            $server = (new ServiceFactory())
            ->setRedirectUriField('callbackLinkSocialUri')
            ->createFromProvider($alias);

            if (!$server->isGetUserStep($this->getRequest())) {
                $this->Flash->error($message);

                return $this->redirect(['action' => 'profile']);
            }
            $data = $server->getUser($this->getRequest());
            $mapper = new MapUser();
            $data = $mapper($server, $data);
            $identity = $this->getRequest()->getAttribute('identity');
            $identity = $identity ?? [];
            $userId = $identity['id'] ?? null;
            $user = $this->getUsersTable()->get($userId);

            $this->getUsersTable()->linkSocialAccount($user, $data);

            if ($user->getErrors()) {
                $this->Flash->error($message);
            } else {
                $this->Flash->success(__d('cake_d_c/users', 'Social account was associated.'));
            }
        } catch (\Exception $e) {
            $log = sprintf(
                'Error linking social account: %s %s',
                $e->getMessage(),
                $e
            );
            $this->log($log);

            $this->Flash->error($message);
        }

        return $this->redirect(['action' => 'profile']);
    }
}

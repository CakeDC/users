<?php
declare(strict_types=1);

/**
 * Copyright 2010 - 2022, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2022, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
namespace CakeDC\Users\Controller\Traits;

use Cake\Core\Configure;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Mailer\Mailer;
use Cake\Mailer\TransportFactory;
use Cake\ORM\TableRegistry;
use CakeDC\Auth\Authentication\AuthenticationService;
use CakeDC\Auth\Authentication\Code2fAuthenticationCheckerFactory;
use CakeDC\Auth\Authentication\Code2fAuthenticationCheckerInterface;
use CakeDC\Auth\Authenticator\TwoFactorAuthenticator;
use CakeDC\Users\Exception\TokenExpiredException;
use CakeDC\Users\Model\Table\OtpCodesTable;

/**
 * Class Code2fTrait
 *
 * @package App\Controller\Traits
 * @mixin \Cake\Controller\Controller
 */
trait Code2fTrait
{
    use U2fTrait {
        redirectWithQuery as redirectWithQuery;
    }

    /**
     * Code2f entry point
     *
     * @return \Cake\Http\Response|null
     */
    public function code2f()
    {
        $data = $this->getCode2fData();
        if (!$data['valid']) {
            return $this->redirectWithQuery([
                'action' => 'login',
            ]);
        }
        if (!$data['registration']) {
            return $this->redirectWithQuery([
                'action' => 'code2fRegister',
            ]);
        }

        return $this->redirectWithQuery([
            'action' => 'code2fAuthenticate',
        ]);
    }

    /**
     * Show Code2f register start step
     *
     * @return \Cake\Http\Response|null
     */
    public function code2fRegister()
    {
        $data = $this->getCode2fData();
        if (!$data['valid']) {
            return $this->redirectWithQuery([
                'action' => 'login',
            ]);
        }
        $field = Configure::read('Code2f.type');
        $this->set('field', $field);
        if ($this->getRequest()->is(['post', 'put'])) {

            $value = $this->getRequest()->getData($field);

            $validated = true;
            if ($data['field'] === Code2fAuthenticationCheckerInterface::CODE2F_TYPE_PHONE) {
                $config = Mailer::getConfig(Configure::read('Code2f.config', 'sms'));
                $phonePattern = TransportFactory::get($config['transport'])->getConfig('phonePattern');
                if (!$phonePattern) {
                    throw new \UnexpectedValueException(__d('cake_d_c/users', 'You must define `phonePattern` in your transport ({0}) config.', $config));
                }
                if (!preg_match($phonePattern, $value)) {
                    $this->Flash->error(__d('cake_d_c/users', 'Invalid phone number: Format must be {0}}', $phonePattern));
                    $validated = false;
                }
            }

            if ($validated) {
                $data['user'][$field] = $value;
                $user = $this->getUsersTable()->saveOrFail($data['user'], ['checkRules' => false]);
                $this->getRequest()->getSession()->write(AuthenticationService::CODE2F_SESSION_KEY, $user);
                $data['registration'] = true;
            }
        }
        if ($data['registration']) {
            return $this->redirectWithQuery([
                'action' => 'code2fAuthenticate',
            ]);
        }
        $this->viewBuilder()->setLayout('CakeDC/Users.login');
    }

    /**
     * Show code2f authenticate start step
     *
     * @return \Cake\Http\Response|null
     */
    public function code2fAuthenticate()
    {
        $data = $this->getCode2fData();
        if (!$data['valid']) {
            return $this->redirectWithQuery(Configure::read('Auth.AuthenticationComponent.loginAction'));
        }
        if (!$data['registration']) {
            return $this->redirectWithQuery([
                'action' => 'code2fRegister',
            ]);
        }
        /** @var OtpCodesTable $OtpCodes */
        $OtpCodes = TableRegistry::getTableLocator()->get('CakeDC/Users.OtpCodes');
        $resend = $this->getRequest()->is(['post', 'put']) && $this->getRequest()->getQuery('resend');
        if ($this->getRequest()->is(['post', 'put']) && !$resend) {
            try {
                $result = $OtpCodes->validateCode2f($data['user']['id'], $this->getRequest()->getData('code'));
                if (!$result) {
                    $this->Flash->error(__d('cake_d_c/users', 'The code entered is not valid, please try again or resend code.'));
                }
                    $this->request->getSession()->delete(AuthenticationService::CODE2F_SESSION_KEY);
                    $this->request->getSession()->write(TwoFactorAuthenticator::USER_SESSION_KEY, $data['user']);
                    return $this->redirectWithQuery(Configure::read('Auth.AuthenticationComponent.loginAction'));
            } catch (TokenExpiredException | \InvalidArgumentException $e) {
                $this->Flash->error($e->getMessage());
            }
        } else {
            try {
                $OtpCodes->sendCode2f($data['user']['id'], $resend);
            } catch (\UnexpectedValueException | RecordNotFoundException | \OverflowException $e) {
                $this->Flash->error($e->getMessage());
            }
            if ($resend) {
                $query = $this->getRequest()->getQueryParams();
                unset($query['resend']);
                $this->setRequest($this->getRequest()->withQueryParams($query));
                return $this->redirectWithQuery(['action' => 'code2fAuthenticate']);
            }
        }
        $this->set($data);
        $this->viewBuilder()->setLayout('CakeDC/Users.login');
    }

    /**
     * Get essential Code2f data
     *
     * @return array
     */
    protected function getCode2fData()
    {
        $data = [
            'valid' => false,
            'user' => null,
            'registration' => null,
            'field' => null
        ];
        $user = $this->getRequest()->getSession()->read(AuthenticationService::CODE2F_SESSION_KEY);
        if (!isset($user['id'])) {
            return $data;
        }
        $entity = $this->getUsersTable()->get($user['id']);
        $data['user'] = $user;
        $data['valid'] = $this->getCode2fAuthenticationChecker()->isEnabled();

        $type = Configure::read('Code2f.type');
        $data['field'] = $type;
        $data['registration'] = !empty($entity[$type]) && (
                ($type === Code2fAuthenticationCheckerInterface::CODE2F_TYPE_PHONE && $entity->phone) ||
                ($type === Code2fAuthenticationCheckerInterface::CODE2F_TYPE_EMAIL && $entity->email)
            );
        $data['verified'] = !empty($entity[$type]) && (
                ($type === Code2fAuthenticationCheckerInterface::CODE2F_TYPE_PHONE && $entity->phone_verified) ||
                ($type === Code2fAuthenticationCheckerInterface::CODE2F_TYPE_EMAIL && $entity->active)
            );
        $data['masked'] = '';
        if ($type === Code2fAuthenticationCheckerInterface::CODE2F_TYPE_PHONE && $entity->phone) {
            $data['masked'] = substr($entity->phone, 0, 3) . '******' . substr($entity->phone, -3);
        } elseif ($type === Code2fAuthenticationCheckerInterface::CODE2F_TYPE_EMAIL && $entity->email) {
            $data['masked'] = preg_replace_callback(
                '/^(.)(.*?)([^@]?)(?=@[^@]+$)/u',
                function ($m) {
                    return $m[1]
                        . str_repeat("*", max(4, mb_strlen($m[2], 'UTF-8')))
                        . ($m[3] ?: $m[1]);
                },
                $entity->email
            );
        }
        return $data;
    }

    /**
     * Get the configured Code2f authentication checker
     *
     * @return \CakeDC\Auth\Authentication\Code2fAuthenticationCheckerInterface
     */
    protected function getCode2fAuthenticationChecker()
    {
        return (new Code2fAuthenticationCheckerFactory())->build();
    }
}

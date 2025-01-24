<?php
declare(strict_types=1);

namespace CakeDC\Users\Model\Behavior;

use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\I18n\DateTime;
use Cake\ORM\Behavior;
use Cake\ORM\Query;
use OutOfBoundsException;

/**
 * One time login link behavior.
 */
class OneTimeLoginLinkBehavior extends Behavior
{
    /**
     * Send a login link to the user.
     *
     * @param string $name User name or email.
     * @return void
     */
    public function sendLoginLink(string $name): void
    {
        $table = $this->table();
        $user = $table->find('byUsernameOrEmail', ['username' => $name])->first();
        if ($user === null) {
            throw new RecordNotFoundException(__('Username not found.'));
        }

        $loginTokenDate = $user->login_token_date ?? null;

        if ($loginTokenDate && $loginTokenDate > DateTime::now()->subSeconds(10)) {
            $this->requestTokenSend($name);
        } else {
            $token = bin2hex(random_bytes(32 / 2));
            $this->table()->updateAll([
                'login_token' => $token,
                'token_send_requested' => 0,
                'login_token_date' => DateTime::now(),
            ], [
                'id' => $user->id,
            ]);
            $user = $this->table()->get($user->id);

            $deliveries = Configure::read('OneTimeLogin.DeliveryHandlers');

            foreach ($deliveries as $deliverySettings) {
                $deliveryClass = $deliverySettings['className'];
                $delivery = new $deliveryClass($deliverySettings['options'] ?? []);
                $delivery->send($user, $token);
            }
        }
    }

    /**
     * Login with a token.
     *
     * @param string $token Token.
     * @return \Cake\Datasource\EntityInterface|null
     */
    public function loginWithToken(string $token): ?EntityInterface
    {
        $lifeTime = Configure::read('Auth.OneTimeLogin.tokenLifeTime', 600);
        $user = $this->table()
            ->find('byOneTimeToken', ['token' => $token])
            ->first();

        if ($user && ($user->login_token_date >= DateTime::now()->subSeconds($lifeTime))) {
            $this->table()->updateAll([
                'login_token' => null,
            ], [
                'id' => $user->id,
            ]);

            return $this->table()->get($user->id);
        }

        return null;
    }

    /**
     * Request a token send.
     *
     * @param string $username Username or email
     * @return void
     */
    public function requestTokenSend(string $username): void
    {
        $user = $this->table()->find('byUsernameOrEmail', ['username' => $username])->first();
        if ($user) {
            $this->table()->updateAll([
                'token_send_requested' => true,
            ], [
                'id' => $user->id,
            ]);
        }
    }

    /**
     * Find by username or email.
     *
     * @param \Cake\ORM\Query $query The query builder.
     * @param array $options Options.
     * @return \Cake\ORM\Query
     */
    public function findByUsernameOrEmail(Query $query, array $options = []): Query
    {
        $username = $options['username'] ?? null;
        if (empty($username)) {
            throw new OutOfBoundsException('Missing username');
        }

        return $query->where([
            'OR' => [
                $this->table()->aliasField('username') => $username,
                $this->table()->aliasField('email') => $username,
            ],
        ]);
    }

    /**
     * Find by token
     *
     * @param \Cake\ORM\Query $query
     * @param array $options
     * @return \Cake\ORM\Query
     */
    public function findByOneTimeToken(Query $query, array $options = []): Query
    {
        $token = $options['token'] ?? null;
        if (empty($token)) {
            throw new OutOfBoundsException('Missing token');
        }

        return $query->where([
            $this->table()->aliasField('login_token') => $token,
        ]);
    }
}

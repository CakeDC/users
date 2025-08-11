<?php
declare(strict_types=1);

namespace CakeDC\Users\Model\Behavior\OneTimeDelivery;

use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Mailer\MailerAwareTrait;

/**
 * Email delivery.
 */
class EmailDelivery implements DeliveryInterface
{
    use MailerAwareTrait;

    /**
     * @var array
     */
    protected array $options;

    /**
     * Constructor
     *
     * @param array $options Delivery options.
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * Send a delivery.
     *
     * @param \CakeDC\Users\Model\Entity\User $user User.
     * @param string $token Token.
     * @return void
     */
    public function send(EntityInterface $user, string $token): void
    {
        $this
            ->getMailer(Configure::read('Users.Email.mailerClass') ?: 'CakeDC/Users.Users')
            ->send('sendToken', [$user, $token]);
    }
}

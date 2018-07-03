<?php
namespace CakeDC\Users\Listener;

use Cake\Event\EventListenerInterface;

class AuthListener implements EventListenerInterface
{
    const EVENT_FAILED_SOCIAL_LOGIN = 'Users.SocialAuth.failedSocialLogin';

    const EVENT_AFTER_SOCIAL_REGISTER = 'Users.SocialAuth.afterRegister';



    /**
     * All implemented events are declared
     *
     * @return array
     */
    public function implementedEvents()
    {
        return [];
    }
}
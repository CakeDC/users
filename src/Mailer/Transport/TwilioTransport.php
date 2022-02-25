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
namespace CakeDC\Users\Mailer\Transport;

use Cake\Core\Configure;
use Cake\Log\Log;
use Cake\Mailer\AbstractTransport;
use Cake\Mailer\Message;
use Twilio\Rest\Client;

class TwilioTransport extends AbstractTransport
{
    protected $_defaultConfig = [
        'phonePattern' => '/^\+[1-9]\d{1,14}$/i'
    ];

    public function send(Message $message): array
    {
        $sid = $this->getConfig('sid');
        $token = $this->getConfig('token');
        $client = new Client($sid, $token);

        $to = $message->getTo();
        $recipients = collection($to);

        $recipients->each(function ($recipient) {
            if (!preg_match($this->getConfig('phonePattern'), $recipient)) {
                throw new \InvalidArgumentException(__d('cake_d_c/users', 'Invalid Recipient {0}: Format must be {1}', $recipient, $this->getConfig('phonePattern')));
            }
        });

        $responses = [];
        foreach ($recipients as $recipient) {
            $content = [
                'from' => $message->getFrom(),
                'body' => $message->getBodyText()
            ];
            if (Configure::read('debug')) {
                $responses[] = $content;
                Log::debug(print_r($content, true));
                continue;
            }
            $responses[] = $client->messages->create(
                $recipient,
                $content
            );
        };
        return $responses;
    }
}

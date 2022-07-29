<?php

declare(strict_types=1);

/**
 * Copyright 2010 - 2019, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2018, Cake Development Corporation (https://www.cakedc.com)
 * @license   MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Test\TestCase\Email;

use Cake\Core\Configure;
use Cake\Mailer\Message;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CakeDC\Users\Mailer\UsersMailer;
use CakeDC\Users\Model\Entity\User;

/**
 * Test Case
 */
class TwilioTransportTest extends TestCase
{

    public function testSendSucces()
    {
        $this->prepareTransport();

        $this->Client->messages->expects($this->any())
            ->method('create')
            ->withConsecutive(
                ['+100000000001', ['from' => ['+100000000000' => '+100000000000'], "body" => "test message"]],
                ['+100000000002', ['from' => ['+100000000000' => '+100000000000'], "body" => "test message"]],
                ['+100000000003', ['from' => ['+100000000000' => '+100000000000'], "body" => "test message"]],
            );

        $message = new \Cake\Mailer\Message();
        $message
            ->setEmailPattern($this->Twilio->getConfig('phonePattern'))
            ->setFrom('+100000000000')
            ->setTo(['+100000000001', '+100000000002', '+100000000003'])
            ->setBodyText('test message');

        Configure::write('debug', false);
        $resp = $this->Twilio->send($message);
        
        $this->assertCount(3, $resp);
    }

    public function testSendInvalidRecipient()
    {
        $this->prepareTransport();

        $message = new \Cake\Mailer\Message();
        $message
            ->setTo('test@example.com')
            ->setBodyText('test message');
    
        $this->expectException(\InvalidArgumentException::class);
        
        $this->Twilio->send($message);
    }


    protected function prepareTransport()
    {
        $this->Twilio = $this->getMockBuilder(\CakeDC\Users\Mailer\Transport\TwilioTransport::class)
            ->onlyMethods(['_getClient'])
            ->getMock();

        $this->Client = $this->getMockBuilder(\Twilio\Rest\Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->Client->messages = $this->getMockBuilder(\Twilio\Rest\Api\V2010\Account\MessageList::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->Twilio->expects($this->any())
            ->method('_getClient')
            ->willReturn($this->Client);
    }

}

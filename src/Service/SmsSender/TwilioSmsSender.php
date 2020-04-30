<?php

namespace App\Service\SmsSender;

use App\Entity\SmsCode;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Api\V2010\Account\MessageInstance;
use Twilio\Rest\Client;

class TwilioSmsSender implements SmsSenderInterface
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var MessageInstance
     */
    private $message;

    /**
     * @var string
     */
    private $number;

    /**
     * TwilioSmsSender constructor.
     *
     * @param Client $client
     * @param string $number
     */
    public function __construct(Client $client, string $number)
    {
        $this->client = $client;
        $this->number = $number;
    }

    /**
     * @param SmsCode $smsCode
     *
     * @throws TwilioException
     */
    public function sendCode(SmsCode $smsCode): void
    {
         $this->message = $this->client->messages->create(
             $smsCode->getPhone(),
             [
                 'from' => $this->number,
                 'body' => 'code ' . $smsCode->getPlainSmsCode(),
             ]
         );
    }

    /**
     * @return MessageInstance
     */
    public function getMessage(): MessageInstance
    {
        return $this->message;
    }

    /**
     * @return array
     */
    public function getResponse(): array
    {
        return $this->message->toArray();
    }
}

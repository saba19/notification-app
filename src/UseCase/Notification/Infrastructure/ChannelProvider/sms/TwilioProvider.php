<?php

namespace App\UseCase\Notification\Infrastructure\ChannelProvider\sms;

use App\UseCase\Notification\Domain\Notification;
use App\UseCase\Notification\Domain\NotificationResponse;
use App\UseCase\Notification\Domain\Service\SmsDomainNotificationChannelProvider;
use App\UseCase\Notification\Infrastructure\ChannelProvider\sms\Response\SmsFailureNotificationResponse;
use App\UseCase\Notification\Infrastructure\ChannelProvider\sms\Response\SmsSuccessNotificationResponse;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Client;

class TwilioProvider implements SmsDomainNotificationChannelProvider
{
    public function __construct(
        private string $twilioAccountSid, private string $twilioAuthToken, private string $twilioNumber)
    {
    }

    public function send(Notification $notification): NotificationResponse
    {
        try {
            $client = new Client($this->twilioAccountSid, $this->twilioAuthToken);
            $client->messages->create(
                $notification->getRecipientPhoneNumber(),
                [
                    'from' => $this->twilioNumber,
                    'body' => $notification->getContent(),
                ]
            );
        } catch (\Exception|TwilioException $exception) {
            return new SmsFailureNotificationResponse();
        }

        return new SmsSuccessNotificationResponse();
    }
}

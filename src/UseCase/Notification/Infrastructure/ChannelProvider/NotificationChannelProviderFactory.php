<?php

namespace App\UseCase\Notification\Infrastructure\ChannelProvider;

use App\UseCase\Notification\Domain\Channel;
use App\UseCase\Notification\Domain\Provider;
use App\UseCase\Notification\Domain\Repository\NotificationProviderRepository;
use App\UseCase\Notification\Domain\Service\DomainNotificationChannelProvider;
use App\UseCase\Notification\Infrastructure\ChannelProvider\email\FakeEmailProvider;
use App\UseCase\Notification\Infrastructure\ChannelProvider\email\SendgridProvider;
use App\UseCase\Notification\Infrastructure\ChannelProvider\sms\FakeSmsProvider;
use App\UseCase\Notification\Infrastructure\ChannelProvider\sms\TwilioProvider;

class NotificationChannelProviderFactory
{
    public function __construct(
        private TwilioProvider $twilioProvider,
        private SendgridProvider $sendgridProvider,
        private FakeSmsProvider $fakeSmsProvider,
        private FakeEmailProvider $fakeEmailProvider,
        private NotificationProviderRepository $notificationProviderRepository,
    ) {
    }

    // todo I know this Factory needs some improvement and changes. It's only to show configuration driven design
    /**
     * @return DomainNotificationChannelProvider[]
     */
    public function getAllEnabledProvidersByChannel(Channel $channel): array
    {
        $smProviders = $this->getForSmsChannel($channel);
        $emailProviders = $this->getForEmailChannel($channel);

        return array_merge($smProviders, $emailProviders);
    }

    /**
     * @return DomainNotificationChannelProvider[]
     */
    private function getForSmsChannel(Channel $channel): array
    {
        if (Channel::SMS !== $channel) {
            return [];
        }

        $providers = [];
        if ($this->notificationProviderRepository->isEnabled(Provider::FAKE_SMS)) {
            $notificationProvider = $this->notificationProviderRepository->getByName(Provider::FAKE_SMS);
            $providers[$notificationProvider->getPosition()] = $this->fakeSmsProvider;
        }

        if ($this->notificationProviderRepository->isEnabled(Provider::TWILIO)) {
            $notificationProvider = $this->notificationProviderRepository->getByName(Provider::TWILIO);
            $providers[$notificationProvider->getPosition()] = $this->twilioProvider;
        }

        ksort($providers);

        return array_values($providers);
    }

    /**
     * @return DomainNotificationChannelProvider[]
     */
    private function getForEmailChannel(Channel $channel): array
    {
        if (Channel::EMAIL !== $channel) {
            return [];
        }
        $providers = [];
        if ($this->notificationProviderRepository->isEnabled(Provider::FAKE_EMAIL)) {
            $notificationProvider = $this->notificationProviderRepository->getByName(Provider::FAKE_EMAIL);
            $providers[$notificationProvider->getPosition()] = $this->fakeEmailProvider;
        }

        if ($this->notificationProviderRepository->isEnabled(Provider::SENDGRID)) {
            $notificationProvider = $this->notificationProviderRepository->getByName(Provider::SENDGRID);
            $providers[$notificationProvider->getPosition()] = $this->sendgridProvider;
        }

        ksort($providers);

        return array_values($providers);
    }
}

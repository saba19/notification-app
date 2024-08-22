<?php

namespace App\DataFixtures;

use App\UseCase\Notification\Domain\Channel;
use App\UseCase\Notification\Domain\NotificationProvider;
use App\UseCase\Notification\Domain\Provider;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $twilioProvider = new NotificationProvider(Provider::TWILIO, Channel::SMS, true, 2);
        $fakeSmsProvider = new NotificationProvider(Provider::FAKE_SMS, Channel::SMS, false, 1);
        $manager->persist($twilioProvider);
        $manager->persist($fakeSmsProvider);

        $sendgridProvider = new NotificationProvider(Provider::SENDGRID, Channel::EMAIL, true, 2);
        $fakeEmailProvider = new NotificationProvider(Provider::FAKE_EMAIL, Channel::EMAIL, false, 1);
        $manager->persist($sendgridProvider);
        $manager->persist($fakeEmailProvider);

        $manager->flush();
    }
}

<?php

namespace App\Tests\Behat\Notification;

use App\Tests\Behat\BaseContext;
use App\UseCase\Notification\Domain\Channel;
use App\UseCase\Notification\Domain\NotificationFactory;
use App\UseCase\Notification\Domain\NotificationProvider;
use App\UseCase\Notification\Domain\Provider;
use App\UseCase\Notification\Domain\User;
use App\UseCase\Notification\Infrastructure\ChannelProvider\email\FakeEmailProvider;
use App\UseCase\Notification\Infrastructure\ChannelProvider\email\Response\EmailSuccessNotificationResponse;
use App\UseCase\Notification\Infrastructure\ChannelProvider\email\SendgridProvider;
use App\UseCase\Notification\Infrastructure\ChannelProvider\sms\FakeSmsProvider;
use App\UseCase\Notification\Infrastructure\ChannelProvider\sms\Response\SmsFailureNotificationResponse;
use App\UseCase\Notification\Infrastructure\ChannelProvider\sms\Response\SmsSuccessNotificationResponse;
use App\UseCase\Notification\Infrastructure\ChannelProvider\sms\TwilioProvider;
use Behat\Gherkin\Node\TableNode;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;

class ResendFailedNotificationContext extends BaseContext
{
    /** @var User[] */
    private array $recipient = [];

    private CommandTester $commandTester;

    public function __construct(private KernelInterface $kernel, EntityManagerInterface $entityManager)
    {
        parent::__construct($kernel, $entityManager);
    }

    /**
     * @BeforeScenario
     */
    public function clearData(): void
    {
        $purger = new ORMPurger($this->getKernel()->getContainer()->get('doctrine')->getManager());
        $purger->purge();
    }

    /**
     * @Given There are following notification providers:
     */
    public function thereAreFollowingNotificationProviders(TableNode $tableNode): void
    {
        $providers = $this->parseTableNode($tableNode);

        foreach ($providers as $provider) {
            $notificationProvider = new NotificationProvider(
                Provider::from($provider['name']),
                Channel::from($provider['channel']),
                'true' === $provider['enabled'],
                $provider['position']
            );

            $this->getEntityManager()->persist($notificationProvider);
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @Given there is an :channel notification :notificationId with status :status
     * @Given there is an :channel notification :notificationId with status :status failed just know
     * @Given there is an :channel notification :notificationId with status :status and last failure attempt :lastFailureAttempt ago
     */
    public function thereIsANotificationWithStatus(string $channel, string $notificationId, string $status, ?string $lastFailureAttempt = null): void
    {
        $notificationFactory = new NotificationFactory();
        $user = $this->createUserIfNotExist('random@email.com', '+48000000000');
        $notification = $notificationFactory->createNotification(
            $notificationId,
            $channel,
            $user,
            'some content',
            'subject',
        );

        if ('sent' === $status) {
            $notification->markAsSent();
        }

        if ('failed' === $status) {
            $notification->markAsFailed();
        }

        if ($lastFailureAttempt) {
            $date = new \DateTimeImmutable();
            $newDateTime = $date->modify("- $lastFailureAttempt");
            $notification->setLastFailureAttemptAt($newDateTime);
        }

        $this->getEntityManager()->persist($notification);
        $this->getEntityManager()->flush();
    }

    /**
     * @When I run :command
     */
    public function iRun(string $command): void
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $command = $application->find($command);
        $this->commandTester = new CommandTester($command);

        $this->commandTester->execute([]);
    }

    private function createUserIfNotExist(string $email, ?string $phoneNumber): User
    {
        $user = $this->recipient[$email] ?? null;
        if (!$user) {
            $user = new User($email, $phoneNumber);
            $this->getEntityManager()->persist($user);
            $this->getEntityManager()->flush();

            $this->recipient[$email] = $user;
        }

        return $user;
    }

    /**
     * @Given the SMS provider :smsProvider is available
     */
    public function theSmsProviderIsAvailable(string $smsProvider): void
    {
        switch ($smsProvider) {
            case 'twilio':
                $mockTwilioProvider = \Mockery::mock(TwilioProvider::class, [
                    'send' => new SmsSuccessNotificationResponse(),
                ]);
                // @phpstan-ignore-next-line
                $this->mockTwilioProvider($mockTwilioProvider);

                break;
            case 'fakeSms':
                $mockFakeSmsProvider = \Mockery::mock(FakeSmsProvider::class, [
                    'send' => new SmsFailureNotificationResponse(),
                ]);
                // @phpstan-ignore-next-line
                $this->mockFakeSmsProvider($mockFakeSmsProvider);
                break;
        }
    }

    /**
     * @Given the Email provider :emailProvider is available
     */
    public function theEmailProviderIsAvailable(string $emailProvider): void
    {
        switch ($emailProvider) {
            case 'sendgrid':
                $mockSendgridProvider = \Mockery::mock(SendgridProvider::class, [
                    'send' => new EmailSuccessNotificationResponse(),
                ]);
                // @phpstan-ignore-next-line
                $this->mockSendgridProvider($mockSendgridProvider);

                break;
            case 'fakeEmail':
                $mockFakeEmailProvider = \Mockery::mock(FakeEmailProvider::class, [
                    'send' => new EmailSuccessNotificationResponse(),
                ]);
                // @phpstan-ignore-next-line
                $this->mockFakeEmailProvider($mockFakeEmailProvider);
                break;
        }
    }

    private function mockFakeSmsProvider(FakeSmsProvider $fakeSmsProvider): void
    {
        $this->getKernel()->getContainer()->set(FakeSmsProvider::class, $fakeSmsProvider);
    }

    private function mockTwilioProvider(TwilioProvider $twilioProvider): void
    {
        $this->getKernel()->getContainer()->set(TwilioProvider::class, $twilioProvider);
    }

    private function mockSendgridProvider(SendgridProvider $sendgridProvider): void
    {
        $this->getKernel()->getContainer()->set(SendgridProvider::class, $sendgridProvider);
    }

    private function mockFakeEmailProvider(FakeEmailProvider $fakeEmailProvider): void
    {
        $this->getKernel()->getContainer()->set(FakeEmailProvider::class, $fakeEmailProvider);
    }

    /**
     * @Then I should see :text
     */
    public function iShouldSee(string $text): void
    {
        $output = $this->commandTester->getDisplay();
        Assert::assertStringContainsString($text, $output);
    }

    /**
     * @Then I should see notification :notificationId has status :status
     */
    public function IShouldSeeNotificationHasStatus(string $notificationId, string $status): void
    {
        $con = $this->getConnection();

        $qb = $con->createQueryBuilder();
        $qb->select('n.status')
            ->from('notifications', 'n')
            ->where('n.id = :notificationId')
            ->setParameter('notificationId', $notificationId);

        $result = $qb->executeQuery()->fetchAllAssociative();
        Assert::assertEquals($status, $result[0]['status']);
    }

    /**
     * @Then the command should finish successfully
     */
    public function theCommandShouldFinishSuccessfully(): void
    {
        Assert::assertEquals(0, $this->commandTester->getStatusCode());
    }
}

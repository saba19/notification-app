<?php

namespace App\Tests\Behat\Notification;

use App\Tests\Behat\BaseContext;
use App\UseCase\Notification\Domain\Channel;
use App\UseCase\Notification\Domain\NotificationProvider;
use App\UseCase\Notification\Domain\Provider;
use App\UseCase\Notification\Domain\User;
use App\UseCase\Notification\Infrastructure\ChannelProvider\email\FakeEmailProvider;
use App\UseCase\Notification\Infrastructure\ChannelProvider\email\Response\EmailFailureNotificationResponse;
use App\UseCase\Notification\Infrastructure\ChannelProvider\email\Response\EmailSuccessNotificationResponse;
use App\UseCase\Notification\Infrastructure\ChannelProvider\email\SendgridProvider;
use Behat\Gherkin\Node\TableNode;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

class EmailContext extends BaseContext
{
    /** @var Response|null */
    private $response;

    /** @var User[] */
    private array $recipient = [];

    private ?int $statusCode;

    private ?string $responseMessage;

    public function __construct(KernelInterface $kernel, EntityManagerInterface $entityManager)
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
     * @Given There is user with email :email
     * @Given There is user with email :email and phone number :phoneNumber
     */
    public function thereIsUserWithEmailAndPhoneNumber(string $email, ?string $phoneNumber = null): void
    {
        $this->createUser($email, $phoneNumber);
    }

    private function createUser(string $email, ?string $phoneNumber): User
    {
        $user = new User($email, $phoneNumber);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        $this->recipient[$email] = $user;

        return $user;
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

    /**
     * @Given the Email provider :emailProvider is not available
     */
    public function theEmailProviderIsNotAvailable(string $emailProvider): void
    {
        switch ($emailProvider) {
            case 'sendgrid':
                $mockSendgridProvider = \Mockery::mock(SendgridProvider::class, [
                    'send' => new EmailFailureNotificationResponse(),
                ]);
                // @phpstan-ignore-next-line
                $this->mockSendgridProvider($mockSendgridProvider);

                break;
            case 'fakeEmail':
                $mockFakeEmailProvider = \Mockery::mock(FakeEmailProvider::class, [
                    'send' => new EmailFailureNotificationResponse(),
                ]);
                // @phpstan-ignore-next-line
                $this->mockFakeEmailProvider($mockFakeEmailProvider);
                break;
        }
    }

    /**
     * @When an external service sends an Email to the user with the following details:
     */
    public function anExternalServiceSendsAnEmailToTheUserWithTheFollowingDetails(TableNode $tableNode): void
    {
        $parsedTableNode = $this->parseTableNode($tableNode)[0];
        $clientEmail = $parsedTableNode['client'];
        $client = $this->recipient[$clientEmail] ?? null;
        $recipientId = $client?->getId();

        $path = '/send-notification';
        $this->response = $this->getKernel()->handle(
            Request::create(
                $path,
                'POST',
                [], [], [], [],
                json_encode(
                    [
                        'recipient' => $recipientId ?? $clientEmail,
                        'channel' => $parsedTableNode['channel'],
                        'subject' => $parsedTableNode['subject'],
                        'content' => $parsedTableNode['content'],
                    ]),
            )
        );
        $this->statusCode = $this->response->getStatusCode();
        $this->responseMessage = $this->response->getContent();
    }

    /**
     * @Then the user :email should have received :number notification with status :status and content :emailContent
     */
    public function theUserShouldHaveReceivedNotificationWithStatusStatusAndContent(string $email, string $number, string $status, string $emailContent): void
    {
        $recipientId = $this->recipient[$email]->getId();
        $con = $this->getConnection();

        $qb = $con->createQueryBuilder();
        $qb->select('n.status, n.content')
            ->from('notifications', 'n')
            ->where('n.user_id = :userId')
            ->where('n.channel = :channel')
            ->setParameter('userId', $recipientId)
            ->setParameter('channel', 'email');

        $result = $qb->executeQuery()->fetchAllAssociative();
        Assert::assertEquals($number, count($result));
        Assert::assertEquals($status, $result[0]['status']);
        Assert::assertEquals($emailContent, $result[0]['content']);
    }

    /**
     * @Then the response status code should be :statusCode
     */
    public function theResponseStatusCodeShouldBe(int $statusCode): void
    {
        Assert::assertSame($statusCode, $this->statusCode);
    }

    /**
     * @Then the response message contains :message
     */
    public function theResponseMessageContains(string $message): void
    {
        $contains = str_contains($this->responseMessage, $message);

        Assert::assertSame(true, $contains);
    }

    private function mockSendgridProvider(SendgridProvider $sendgridProvider): void
    {
        $this->getKernel()->getContainer()->set(SendgridProvider::class, $sendgridProvider);
    }

    private function mockFakeEmailProvider(FakeEmailProvider $fakeEmailProvider): void
    {
        $this->getKernel()->getContainer()->set(FakeEmailProvider::class, $fakeEmailProvider);
    }
}

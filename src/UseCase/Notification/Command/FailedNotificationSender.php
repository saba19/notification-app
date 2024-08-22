<?php

namespace App\UseCase\Notification\Command;

use App\UseCase\Notification\Application\SendNotification;
use App\UseCase\Notification\Domain\Repository\NotificationRepository;
use App\UseCase\Shared\Domain\Bus\CommandBus;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:resent-failed-notification',
    description: 'Send failed notification',
    hidden: false,
)]
class FailedNotificationSender extends Command
{
    public function __construct(
        private readonly NotificationRepository $notificationRepository,
        private readonly CommandBus $commandBus,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $notificationsToSend = $this->notificationRepository->getNotificationToResend();

        if (empty($notificationsToSend)) {
            $output->writeln('No notification to resend');
        }

        foreach ($notificationsToSend as $notification) {
            try {
                $output->writeln('Sending notification with id'.$notification->getId());
                $this->commandBus->dispatch(new SendNotification($notification->getId()));
            } catch (\Exception $exception) {
                $output->writeln($exception->getMessage());
                continue;
            }
        }

        return 0;
    }
}

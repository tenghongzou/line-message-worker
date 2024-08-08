<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\BeanstalkdService;
use App\Service\LineNotifyService;
use LeoCarmo\GracefulShutdown\GracefulShutdown;
use Pheanstalk\Pheanstalk;
use Pheanstalk\Values\TubeName;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:send-message-to-line', description: 'Get messages from Beanstalkd and send to Line')]
class SendMessageToLineCommand extends Command
{
    const BEANSTALKD_TUBE_NAME = 'line-tube';


    public function __construct(
        private BeanstalkdService $beanstalkdService,
        private LineNotifyService $lineNotifyService,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $shutdown = new GracefulShutdown();

        $tube       = new TubeName(self::BEANSTALKD_TUBE_NAME);
        $pheanstalk = $this->beanstalkdService->getPheanstalk();
        $pheanstalk->watch($tube);

        while (!$shutdown->signalReceived()) {
            $job = $pheanstalk->reserveWithTimeout(10);
            try {
                if (!$job) {
                    continue;
                }

                $data        = json_decode($job->getData(), true);
                $messageText = $data['message'];

                try {
                    $this->lineNotifyService->sendMessage($messageText);
                    $output->writeln('Message sent successfully!');
                    $pheanstalk->delete($job);
                } catch (\Exception $e) {
                    $output->writeln('Failed to send message: ' . $e->getMessage());
                    $pheanstalk->release($job);
                }
            } catch (\Exception $e) {
                $output->writeln('Failed to Job: ' . $e->getMessage());
                $pheanstalk->release($job);
            }
        }

        $output->writeln('Shutting down gracefully.');
        return Command::SUCCESS;
    }

}

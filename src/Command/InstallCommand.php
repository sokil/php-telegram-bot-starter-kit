<?php
declare(strict_types=1);

namespace Celebrator\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;

class InstallCommand extends Command
{
    public static $defaultName = 'install';

    /**
     * @var Telegram
     */
    private $telegram;

    /**
     * @param Telegram $telegram
     */
    public function __construct(Telegram $telegram)
    {
        parent::__construct(null);

        $this->telegram = $telegram;
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('install')
            ->setDescription('Configure bot with web hooks');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return null|int null or 0 if everything went fine, or an error code
     *
     * @see setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $webHookUrl = getenv('TELEGRAM_BOT_WEBHOOK_URL');

        try {
            // Set web hook
            $result = $this->telegram->setWebhook($webHookUrl);
            if ($result->isOk()) {
                echo $result->getDescription();
            } else {
                $output->writeln(sprintf('<error>%s</error>', 'Unknown error'));
            }
        } catch (TelegramException $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
        }

        return 0;
    }
}
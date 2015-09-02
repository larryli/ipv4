<?php
/**
 * InitCommand.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\IPv4\Command;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InitCommand extends Command
{
    private $progress = null;

    protected function configure()
    {
        $this->setName('init')
            ->setDescription('initialize ip database')
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force to initialize(download qqwry.dat & 17monipdb.dat if not exist & generate new database)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $force = $input->getOption('force');
        $output->writeln("<info>initialize ip database:</info>");
        $this->download($output, '17monipdb', $force);
        $this->download($output, 'qqwry', $force);
    }

    protected function download($output, $name, $force)
    {
        $ipdb = $this->newIPDB($name);
        $filename = basename($ipdb->filename);
        if (!$force && file_exists($ipdb->filename)) {
            $output->writeln("<comment>use exist {$filename} file.</comment>", OutputInterface::VERBOSITY_VERBOSE);
        } else {
            $output->writeln("<info>download {$filename} file:</info>");
            $ipdb->download(function ($url) use ($output) {
                return file_get_contents($url, false, $this->createStreamContext($output));
            });
            $output->writeln('<info> completed!</info>');
        }
        return $ipdb;
    }

    /**
     * @param OutputInterface $output
     *
     * @return resource
     */
    protected function createStreamContext(OutputInterface $output)
    {
        $ctx = stream_context_create([], [
            'notification' => function ($code, $severity, $message, $message_code, $bytesTransferred, $bytesMax) use ($output) {
                switch ($code) {
                    case STREAM_NOTIFY_FILE_SIZE_IS:
                        $this->progress = new ProgressBar($output, $bytesMax);
                        $this->progress->start();
                        break;
                    case STREAM_NOTIFY_PROGRESS:
                        $this->progress->setProgress($bytesTransferred);
                        if ($bytesTransferred == $bytesMax) {
                            $this->progress->finish();
                            $output->writeln('');
                        }
                        break;
                    case STREAM_NOTIFY_COMPLETED:
                        $this->progress->finish();
                        break;
                }
            }
        ]);
        return $ctx;
    }
}

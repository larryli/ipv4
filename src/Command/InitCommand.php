<?php
/**
 * InitCommand.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\Command;

use larryli\ipv4\Query\Query;
use larryli\ipv4\Query\DatabaseQuery;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class InitCommand
 * @package larryli\ipv4\Command
 */
class InitCommand extends Command
{
    /**
     * @var ProgressBar|null
     */
    private $progress = null;

    /**
     *
     */
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

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $force = $input->getOption('force');
        $output->writeln("<info>initialize ip database:</info>");
        foreach (Query::config() as $query => $provider) {
            if (empty($provider)) {
                $this->download($output, $query, $force);
            } else {
                $this->division($output);
                $this->generate($output, $query, $force, $provider);
            }
        }
    }

    /**
     * @param OutputInterface $output
     */
    protected function division(OutputInterface $output)
    {
        DatabaseQuery::initDivision(function ($code, $n) use ($output) {
            switch ($code) {
                case 0:
                    $output->writeln("<info>generate divisions table:</info>");
                    $this->progress = new ProgressBar($output, $n);
                    $this->progress->start();
                    break;
                case 1:
                    $this->progress->setProgress($n);
                    break;
                case 2:
                    $this->progress->finish();
                    $output->writeln('<info> completed!</info>');
                    break;
            }
        }, true);
    }

    /**
     * @param OutputInterface $output
     * @param string $name
     * @param bool $force
     * @param string|array $provider
     * @return void
     * @throws \Exception
     */
    protected function generate(OutputInterface $output, $name, $force, $provider)
    {
        $query = Query::create($name);
        if (is_string($provider)) {
            $provider = Query::create($provider);
            $use = $provider->name();
            $provider_extra = null;
        } else if (is_array($provider)) {
            $provider_extra = Query::create($provider[1]);
            $provider = Query::create($provider[0]);
            $use = $provider->name() . ' and ' . $provider_extra->name();
        } else {
            throw new \Exception("Error generate options {$provider}");
        }
        $name = $query->name();
        if (!$force && $query->exists()) {
            $output->writeln("<comment>use exist {$name} table.</comment>", OutputInterface::VERBOSITY_VERBOSE);
        } else {
            $output->writeln("<info>generate {$name} table with {$use}:</info>");
            $query->generate(function ($code, $n) use ($output) {
                switch ($code) {
                    case 0:
                        $this->progress = new ProgressBar($output, $n);
                        $this->progress->start();
                        break;
                    case 1:
                        $this->progress->setProgress($n);
                        break;
                    case 2:
                        $this->progress->finish();
                        break;
                }
            }, $provider, $provider_extra);
            $output->writeln('<info> completed!</info>');
        }
    }

    /**
     * @param OutputInterface $output
     * @param string $name
     * @param bool $force
     * @return void
     * @throws \Exception
     */
    protected function download(OutputInterface $output, $name, $force)
    {
        $query = Query::create($name);
        $name = $query->name();
        if (!$force && $query->exists()) {
            $output->writeln("<comment>use exist {$name} file or api.</comment>", OutputInterface::VERBOSITY_VERBOSE);
        } else {
            $output->writeln("<info>download {$name} file:</info>");
            $query->generate(function ($url) use ($output) {
                return file_get_contents($url, false, $this->createStreamContext($output));
            });
            $output->writeln('<info> completed!</info>');
        }
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

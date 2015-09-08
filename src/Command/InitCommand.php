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
     * @var null
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
     * @param $output
     */
    protected function division($output)
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

    protected function generate($output, $name, $force, $provider)
    {
        $query = Query::create($name);
        if (is_string($provider)) {
            $db1 = Query::create($provider);
            $use = $db1->name();
            $db2 = null;
        } else if (is_array($provider)) {
            $db1 = Query::create($provider[0]);
            $db2 = Query::create($provider[1]);
            $use = $db1->name() . ' and ' . $db2->name();
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
            }, $db1, $db2);
            $output->writeln('<info> completed!</info>');
        }
        return $query;
    }

    /**
     * @param $output
     * @param $name
     * @param $force
     * @return \larryli\ipv4\Query\MonIPDBQuery|\larryli\ipv4\Query\QQWryQuery
     * @throws \Exception
     */
    protected function download($output, $name, $force)
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
        return $query;
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

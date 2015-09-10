<?php
/**
 * BenchmarkCommand.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\commands;

use larryli\ipv4\query\Query;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class BenchmarkCommand
 * @package larryli\ipv4\commands
 */
class BenchmarkCommand extends Command
{
    /**
     *
     */
    protected function configure()
    {
        $this->setName('benchmark')
            ->setDescription('benchmark')
            ->addOption(
                'times',
                't',
                InputOption::VALUE_OPTIONAL,
                'number of times',
                100000
            )
            ->addArgument(
                'type',
                InputArgument::OPTIONAL,
                "file or database",
                'all');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $times = $input->getOption('times');
        if ($times < 1) {
            $output->writeln("<error>benchmark {$times} is too small</error>");
            return;
        }
        $type = $input->getArgument('type');
        $output->writeln("<info>benchmark {$type}:</info>\t<comment>{$times} times</comment>");
        switch ($type) {
            case 'all':
                foreach (Query::config() as $name => $provider) {
                    $this->benchmark($output, $name, $times);
                }
                break;
            case 'file':
                foreach (Query::config() as $name => $provider) {
                    if (empty($provider)) {
                        $this->benchmark($output, $name, $times);
                    }
                }
                break;
            case 'database':
                foreach (Query::config() as $name => $provider) {
                    if (!empty($provider)) {
                        $this->benchmark($output, $name, $times);
                    }
                }
                break;
            default:
                $output->writeln("<error>Unknown type \"{$type}\".</error>");
                break;
        }
    }

    /**
     * @param OutputInterface $output
     * @param string $name
     * @param integer $times
     * @throws \Exception
     */
    private function benchmark(OutputInterface $output, $name, $times)
    {
        $query = Query::create($name);
        if (count($query) > 0) {
            $output->write("\t<info>benchmark {$name}:</info> \t");
            $start = microtime(true);
            for ($i = 0; $i < $times; $i++) {
                $ip = mt_rand(0, 4294967295);
                $query->find($ip);
            }
            $time = microtime(true) - $start;
            $output->writeln("<comment>{$time} secs</comment>");
        }
    }

}

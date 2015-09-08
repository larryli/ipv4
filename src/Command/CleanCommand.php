<?php
/**
 * CleanCommand.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\Command;

use larryli\ipv4\Query\Query;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CleanCommand
 * @package larryli\ipv4\Command
 */
class CleanCommand extends Command
{
    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName('clean')
            ->setDescription('clean ip database')
            ->addArgument(
                'type',
                InputArgument::OPTIONAL,
                "file or database",
                'all');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cleanDivision = false;
        $type = $input->getArgument('type');
        $output->writeln("<info>clean {$type}:</info>");
        switch ($type) {
            case 'all':
                foreach (Query::config() as $query => $provider) {
                    if (!empty($provider)) {
                        $cleanDivision = true;
                    }
                    $this->clean($output, $query);
                }
                break;
            case 'file':
                foreach (Query::config() as $query => $provider) {
                    if (empty($provider)) {
                        $this->clean($output, $query);
                    }
                }
                break;
            case 'database':
                foreach (Query::config() as $query => $provider) {
                    if (!empty($provider)) {
                        $cleanDivision = true;
                        $this->clean($output, $query);
                    }
                }
                break;
            default:
                $output->writeln("<error>Unknown type \"{$type}\".</error>");
                break;
        }
        if ($cleanDivision) {
            $this->cleanDivision($output);
        }
    }

    /**
     * @param $output
     * @param $name
     * @throws \Exception
     */
    private function clean($output, $name)
    {
        $output->write("<info>clean {$name}:</info>");
        $query = Query::create($name);
        $query->clean();
        $output->writeln('<info> completed!</info>');
    }

    /**
     * @param $output
     */
    private function cleanDivision($output)
    {
        $output->write("<info>clean divisions:</info>");
        \larryli\ipv4\Query\DatabaseQuery::cleanDivision();
        $output->writeln('<info> completed!</info>');
    }

}

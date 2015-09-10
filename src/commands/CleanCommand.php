<?php
/**
 * CleanCommand.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\commands;

use larryli\ipv4\query\Query;
use larryli\ipv4\query\DatabaseQuery;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CleanCommand
 * @package larryli\ipv4\commands
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
     * @return void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cleanDivision = false;
        $type = $input->getArgument('type');
        $output->writeln("<info>clean {$type}:</info>");
        switch ($type) {
            case 'all':
                foreach (Query::config() as $name => $provider) {
                    if (!empty($provider)) {
                        $cleanDivision = true;
                    }
                    $this->clean($output, $name);
                }
                break;
            case 'file':
                foreach (Query::config() as $name => $provider) {
                    if (empty($provider)) {
                        $this->clean($output, $name);
                    }
                }
                break;
            case 'database':
                foreach (Query::config() as $name => $provider) {
                    if (!empty($provider)) {
                        $cleanDivision = true;
                        $this->clean($output, $name);
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
     * @param OutputInterface $output
     * @param string $name
     * @throws \Exception
     */
    private function clean(OutputInterface $output, $name)
    {
        $output->write("<info>clean {$name}:</info>");
        $query = Query::create($name);
        $query->clean();
        $output->writeln('<info> completed!</info>');
    }

    /**
     * @param OutputInterface $output
     */
    private function cleanDivision(OutputInterface $output)
    {
        $output->write("<info>clean divisions:</info>");
        DatabaseQuery::cleanDivision();
        $output->writeln('<info> completed!</info>');
    }

}

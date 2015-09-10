<?php
/**
 * QueryCommand.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\commands;

use larryli\ipv4\query\Query;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class QueryCommand
 * @package larryli\ipv4\commands
 */
class QueryCommand extends Command
{
    /**
     *
     */
    protected function configure()
    {
        $this->setName('query')
            ->setDescription('query ip')
            ->addArgument(
                'ip',
                InputArgument::REQUIRED,
                "ip v4 address");
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ip = $input->getArgument('ip');
        $output->writeln("<info>query \"{$ip}\":</info>");
        $ip = ip2long($ip);
        foreach (Query::providers() as $name => $provider) {
            $this->query($output, $name, $ip);
        }
    }

    /**
     * @param OutputInterface $output
     * @param string $name
     * @param integer $ip
     * @throws \Exception
     */
    private function query(OutputInterface $output, $name, $ip)
    {
        $query = Query::create($name);
        $address = $query->find($ip);
        $output->writeln("\t<comment>{$name}:</comment> {$address}");
    }
}

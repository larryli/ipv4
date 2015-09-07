<?php
/**
 * QueryCommand.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class QueryCommand
 * @package larryli\ipv4\Command
 */
class QueryCommand extends Command
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
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ip = $input->getArgument('ip');
        $output->writeln("<info>query \"{$ip}\":</info>");
        $ip = ip2long($ip);
        $this->query($output, '17monipdb', $ip);
        $this->query($output, 'qqwry', $ip);
        $this->query($output, 'full', $ip);
        $this->query($output, 'mini', $ip);
        $this->query($output, 'china', $ip);
        $this->query($output, 'world', $ip);
    }

    /**
     * @param $output
     * @param $name
     * @param $ip
     * @throws \Exception
     */
    private function query($output, $name, $ip)
    {
        $query = $this->newQuery($name);
        $address = $query->query($ip);
        $output->writeln("\t<comment>{$name}:</comment> {$address}");
    }

}

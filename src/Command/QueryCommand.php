<?php
/**
 * InitCommand.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class QueryCommand extends Command
{
    private $progress = null;

    protected function configure()
    {
        $this->setName('query')
            ->setDescription('query ip')
            ->addArgument(
                'ip',
                InputArgument::REQUIRED,
                "ip v4 address");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ip = $input->getArgument('ip');
        $output->writeln("<info>query \"{$ip}\":</info>");
        $ip = ip2long($ip);
        $this->query($output, '17monipdb', $ip);
        $this->query($output, 'qqwry', $ip);
    }

    private function query($output, $name, $ip)
    {
        $ipdb = $this->newIPDB($name);
        $address = $ipdb->query($ip);
        $output->writeln("\t<comment>{$name}:</comment> {$address}");
    }

}

<?php
/**
 * InitCommand.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\IPv4\Command;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DumpCommand extends Command
{
    private $progress = null;

    protected function configure()
    {
        $this
            ->setName('dump')
            ->setDescription('dump ip database')
            ->addArgument(
                'type',
                InputArgument::OPTIONAL,
                "address or guess",
                'default');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $type = $input->getArgument('type');
        $output->writeln("<info>dump {$type}:</info>");
        switch ($type) {
            case 'default':
                $this->dumpDefault($output, '17monipdb', 'dump_17monipdb.json');
                $this->dumpDefault($output, 'qqwry', 'dump_qqwry.json');
                break;
            case 'address':
                $this->dumpAddress($output, '17monipdb', 'dump_17monipdb_address.json');
                $this->dumpAddress($output, 'qqwry', 'dump_qqwry_address.json');
                break;
            case 'guess':
                $this->dumpGuess($output, '17monipdb', 'dump_17monipdb_guess.json');
                $this->dumpGuess($output, 'qqwry', 'dump_qqwry_guess.json');
                break;
            default:
                throw new \Exception("Unknown type \"{$type}\".");
        }
    }

    private function dumpDefault($output, $name, $filename)
    {
        $result = [];
        $ipdb = $this->newIPDB($name);
        $this->dump($output, $ipdb, $filename, function ($output, $ipdb) use (&$result) {
            $ipdb->dump(function ($ip, $address) use ($output, &$result) {
                static $n = 0;
                $result[long2ip($ip)] = $address;
                $n++;
                if (($n % 250) == 0) {
                    $this->progress->setProgress($n);
                }
            });
        });
        $this->write($output, $filename, $result);
    }

    private function dumpAddress($output, $name, $filename)
    {
        $ipdb = $this->newIPDB($name);
        $result = $this->address($output, $ipdb, $filename);
        $this->write($output, $filename, $result);
    }

    private function dumpGuess($output, $name, $filename)
    {
        $ipdb = $this->newIPDB($name);
        $addresses = $this->address($output, $ipdb, $filename);
        $result = $this->guess($output, $ipdb, $filename, $addresses);
        $this->write($output, $filename, $result);
    }

    private function dump($output, $ipdb, $filename, $func)
    {
        $output->writeln("<info>dump {$filename}:</info>");
        $this->progress = new ProgressBar($output, $ipdb->getTotal());
        $this->progress->start();
        $func($output, $ipdb);
        $this->progress->finish();
        $output->writeln('<info> completed!</info>');
    }

    private function address($output, $ipdb, $filename)
    {
        $addresses = [];
        $this->dump($output, $ipdb, $filename, function ($output, $ipdb) use (&$addresses) {
            $ipdb->dump(function ($ip, $address) use ($output, &$addresses) {
                static $n = 0;
                if (!in_array($address, $addresses)) {
                    $addresses[] = $address;
                }
                $n++;
                if (($n % 250) == 0) {
                    $this->progress->setProgress($n);
                }
            });
        });
        sort($addresses);
        return $addresses;
    }

    private function guess($output, $ipdb, $filename, $addresses)
    {
        $result = [];
        $output->writeln("<info>guess {$filename}:</info>");
        $this->progress = new ProgressBar($output, count($addresses));
        $this->progress->start();
        foreach ($addresses as $address) {
            static $n = 0;
            list($id, $name) = $ipdb->guess($address);
            $result[$address] = "{$id} {$name}";
            $n++;
            if (($n % 10) == 0) {
                $this->progress->setProgress($n);
            }
        }
        $this->progress->finish();
        $output->writeln('<info> completed!</info>');
        return $result;
    }

    private function write($output, $filename, $result)
    {
        $output->write("<info>write {$filename}:</info>");
        file_put_contents($filename, json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        $output->writeln('<info> completed!</info>');
    }

}

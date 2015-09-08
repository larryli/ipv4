<?php
/**
 * DumpCommand.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\Command;

use larryli\ipv4\Query\Query;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DumpCommand
 * @package larryli\ipv4\Command
 */
class DumpCommand extends Command
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
        $this
            ->setName('dump')
            ->setDescription('dump ip database')
            ->addArgument(
                'type',
                InputArgument::OPTIONAL,
                "address or guess",
                'default');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $type = $input->getArgument('type');
        $output->writeln("<info>dump {$type}:</info>");
        switch ($type) {
            case 'default':
                foreach (Query::config() as $query => $options) {
                    $this->dumpDefault($output, $query, 'dump_' . $query . '.json');
                }
                break;
            case 'address':
                foreach (Query::config() as $query => $options) {
                    $this->dumpAddress($output, $query, 'dump_' . $query . '_address.json');
                }
                break;
            case 'guess':
                foreach (Query::config() as $query => $options) {
                    if (empty($options)) {
                        $this->dumpGuess($output, $query, 'dump_' . $query . '_guess.json');
                    }
                }
                break;
            default:
                $output->writeln("<error>Unknown type \"{$type}\".</error>");
                break;
        }
    }

    /**
     * @param $output
     * @param $name
     * @param $filename
     * @throws \Exception
     */
    private function dumpDefault($output, $name, $filename)
    {
        $result = [];
        $query = Query::create($name);
        $this->dump($output, $query, $filename, function ($output, $query) use (&$result) {
            $query->dump(function ($ip, $address) use ($output, &$result) {
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

    /**
     * @param $output
     * @param $name
     * @param $filename
     * @throws \Exception
     */
    private function dumpAddress($output, $name, $filename)
    {
        $query = Query::create($name);
        $result = $this->address($output, $query, $filename);
        $this->write($output, $filename, $result);
    }

    /**
     * @param $output
     * @param $name
     * @param $filename
     * @throws \Exception
     */
    private function dumpGuess($output, $name, $filename)
    {
        $query = Query::create($name);
        $addresses = $this->address($output, $query, $filename);
        $result = $this->guess($output, $query, $filename, $addresses);
        $this->write($output, $filename, $result);
    }

    /**
     * @param $output
     * @param $query
     * @param $filename
     * @param $func
     */
    private function dump($output, $query, $filename, $func)
    {
        $output->writeln("<info>dump {$filename}:</info>");
        $this->progress = new ProgressBar($output, $query->getTotal());
        $this->progress->start();
        $func($output, $query);
        $this->progress->finish();
        $output->writeln('<info> completed!</info>');
    }

    /**
     * @param $output
     * @param $query
     * @param $filename
     * @return array
     */
    private function address($output, $query, $filename)
    {
        $addresses = [];
        $this->dump($output, $query, $filename, function ($output, $query) use (&$addresses) {
            $query->dump(function ($_, $address) use ($output, &$addresses) {
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

    /**
     * @param $output
     * @param $query
     * @param $filename
     * @param $addresses
     * @return array
     */
    private function guess($output, $query, $filename, $addresses)
    {
        $result = [];
        $output->writeln("<info>guess {$filename}:</info>");
        $this->progress = new ProgressBar($output, count($addresses));
        $this->progress->start();
        foreach ($addresses as $address) {
            static $n = 0;
            list($result[$address], $_) = $query->guess($address);
            $n++;
            if (($n % 10) == 0) {
                $this->progress->setProgress($n);
            }
        }
        $this->progress->finish();
        $output->writeln('<info> completed!</info>');
        return $result;
    }

    /**
     * @param $output
     * @param $filename
     * @param $result
     */
    private function write($output, $filename, $result)
    {
        $output->write("<info>write {$filename}:</info>");
        file_put_contents($filename, json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        $output->writeln('<info> completed!</info>');
    }

}

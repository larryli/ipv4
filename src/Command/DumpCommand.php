<?php
/**
 * DumpCommand.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\Command;

use larryli\ipv4\Query\Query;
use larryli\ipv4\Query\FileQuery;
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
     * @var ProgressBar|null
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
     * @return void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $type = $input->getArgument('type');
        $output->writeln("<info>dump {$type}:</info>");
        switch ($type) {
            case 'default':
                foreach (Query::config() as $query => $provider) {
                    $this->dumpDefault($output, $query, 'dump_' . $query . '.json');
                }
                break;
            case 'address':
                foreach (Query::config() as $query => $provider) {
                    $this->dumpAddress($output, $query, 'dump_' . $query . '_address.json');
                }
                break;
            case 'guess':
                foreach (Query::config() as $query => $provider) {
                    if (empty($provider)) {
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
    private function dumpDefault(OutputInterface $output, $name, $filename)
    {
        $result = [];
        $query = Query::create($name);
        if ($query->total() > 0) {
            $this->dump($output, $query, $filename, function (Query $query) use (&$result) {
                $query->dump(function ($ip, $address) use (&$result) {
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
    }

    /**
     * @param OutputInterface $output
     * @param string $name
     * @param string $filename
     * @throws \Exception
     */
    private function dumpAddress(OutputInterface $output, $name, $filename)
    {
        $query = Query::create($name);
        if ($query->total() > 0) {
            $result = $this->address($output, $query, $filename, 'dump_' . $name . '.json');
            $this->write($output, $filename, $result);
        }
    }

    /**
     * @param OutputInterface $output
     * @param string $name
     * @param string $filename
     * @throws \Exception
     */
    private function dumpGuess(OutputInterface $output, $name, $filename)
    {
        $query = Query::create($name);
        if ($query->total() > 0) {
            $json_filename = 'dump_' . $name . '_address.json';
            if (file_exists($json_filename)) {
                $addresses = $this->read($output, $json_filename);
            } else {
                $addresses = $this->address($output, $query, $filename, 'dump_' . $name . '.json');
            }
            $result = $this->guess($output, $query, $filename, $addresses);
            $this->write($output, $filename, $result);
        }
    }

    /**
     * @param OutputInterface $output
     * @param Query $query
     * @param string $filename
     * @param callable $func
     */
    private function dump(OutputInterface $output, Query $query, $filename, callable $func)
    {
        $output->writeln("<info>dump {$filename}:</info>");
        $this->progress = new ProgressBar($output, $query->total());
        $this->progress->start();
        $func($query);
        $this->progress->finish();
        $output->writeln('<info> completed!</info>');
    }

    /**
     * @param OutputInterface $output
     * @param Query $query
     * @param string $filename
     * @param string $json_filename
     * @return array
     */
    private function address(OutputInterface $output, Query $query, $filename, $json_filename)
    {
        if (file_exists($json_filename)) {
            $addresses = array_unique(array_values($this->read($output, $json_filename)));
        } else {
            $addresses = [];
            $this->dump($output, $query, $filename, function (Query $query) use (&$addresses) {
                $query->dump(function ($_, $address) use (&$addresses) {
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
        }
        sort($addresses);
        return $addresses;
    }

    /**
     * @param OutputInterface $output
     * @param FileQuery $query
     * @param string $filename
     * @param string[] $addresses
     * @return array
     */
    private function guess(OutputInterface $output, FileQuery $query, $filename, $addresses)
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
     * @param OutputInterface $output
     * @param string $filename
     * @param string[] $result
     */
    private function write(OutputInterface $output, $filename, $result)
    {
        $output->write("<info>write {$filename}:</info>");
        file_put_contents($filename, json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        $output->writeln('<info> completed!</info>');
    }

    /**
     * @param OutputInterface $output
     * @param string $filename
     * @return string[] $result
     */
    private function read(OutputInterface $output, $filename)
    {
        $output->write("<info>read {$filename}:</info>");
        $result = json_decode(file_get_contents($filename), true);
        $output->writeln('<info> completed!</info>');
        return $result;
    }
}

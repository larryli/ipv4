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
                "division or division_id",
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
            case 'division':
                foreach (Query::config() as $query => $provider) {
                    $this->dumpDivision($output, $query, 'dump_' . $query . '_division.json');
                }
                break;
            case 'division_id':
                foreach (Query::config() as $query => $provider) {
                    if (empty($provider)) {
                        $this->dumpDivisionId($output, $query, 'dump_' . $query . '_division_id.json');
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
     * @param $name
     * @param $filename
     * @throws \Exception
     */
    private function dumpDefault(OutputInterface $output, $name, $filename)
    {
        $query = Query::create($name);
        $result = $this->dump($output, $query, $filename);
        if (count($result) > 0) {
            $this->write($output, $filename, $result);
        }
    }

    /**
     * @param OutputInterface $output
     * @param Query $query
     * @param $filename
     * @return array
     */
    private function dump(OutputInterface $output, Query $query, $filename)
    {
        $result = [];
        if (count($query) > 0) {
            $output->writeln("<info>dump {$filename}:</info>");
            $this->progress = new ProgressBar($output, count($query));
            $this->progress->start();
            $n = 0;
            $time = Query::time();
            foreach ($query as $ip => $division) {
                if (is_integer($division)) {
                    $division = $query->string($division);
                }
                $result[long2ip($ip)] = $division;
                $n++;
                if ($time < Query::time()) {
                    $this->progress->setProgress($n);
                    $time = Query::time();
                }
            }
            $this->progress->finish();
            $output->writeln('<info> completed!</info>');
        }
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
     * @param string $name
     * @param string $filename
     * @throws \Exception
     */
    private function dumpDivision(OutputInterface $output, $name, $filename)
    {
        $query = Query::create($name);
        $result = $this->divisions($output, $query, $filename, 'dump_' . $name . '.json');
        if (count($result) > 0) {
            $result = array_unique(array_values($result));
            sort($result);
            $this->write($output, $filename, $result);
        }
    }

    /**
     * @param OutputInterface $output
     * @param Query $query
     * @param $filename
     * @param $json_filename
     * @return array|\string[]
     */
    private function divisions(OutputInterface $output, Query $query, $filename, $json_filename)
    {
        if (file_exists($json_filename)) {
            $result = $this->read($output, $json_filename);
        } else {
            $result = $this->dump($output, $query, $filename);
        }
        return $result;
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

    /**
     * @param OutputInterface $output
     * @param string $name
     * @param string $filename
     * @throws \Exception
     */
    private function dumpDivisionId(OutputInterface $output, $name, $filename)
    {
        $query = Query::create($name);
        $json_filename = 'dump_' . $name . '_division.json';
        if (file_exists($json_filename)) {
            $result = $this->read($output, $json_filename);
        } else {
            $result = $this->divisions($output, $query, $filename, 'dump_' . $name . '.json');
        }
        if (count($result) > 0) {
            $result = $this->division_ids($output, $query, $result);
            $this->write($output, $filename, $result);
        }
    }

    /**
     * @param OutputInterface $output
     * @param FileQuery $query
     * @param string[] $divisions
     * @return array
     */
    private function division_ids(OutputInterface $output, FileQuery $query, $divisions)
    {
        $result = [];
        $output->writeln("<info>translate division to division_id:</info>");
        $this->progress = new ProgressBar($output, count($divisions));
        $this->progress->start();
        $n = 0;
        $time = Query::time();
        foreach ($divisions as $division) {
            $result[$division] = $query->integer($division);
            $n++;
            if ($time < Query::time()) {
                $this->progress->setProgress($n);
                $time = Query::time();
            }
        }
        $this->progress->finish();
        $output->writeln('<info> completed!</info>');
        return $result;
    }
}

<?php
/**
 * DumpCommand.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\commands;

use larryli\ipv4\query\Query;
use larryli\ipv4\query\FileQuery;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DumpCommand
 * @package larryli\ipv4\commands
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
                "division or division_id or count",
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
                foreach (Query::providers() as $name => $provider) {
                    $this->dumpDefault($output, $name, 'dump_' . $name . '.json');
                }
                break;
            case 'division':
                foreach (Query::providers() as $name => $provider) {
                    $this->dumpDivision($output, $name, 'dump_' . $name . '_division.json');
                }
                break;
            case 'division_id':
                foreach (Query::providers() as $name => $provider) {
                    if (empty($provider)) {
                        $this->dumpDivisionWithId($output, $name, 'dump_' . $name . '_division_id.json');
                    }
                }
                break;
            case 'count':
                foreach (Query::providers() as $name => $provider) {
                    $this->dumpCount($output, $name, 'dump_' . $name . '_count.json');
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
                    $division = $query->divisionById($division);
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
        $result = array_unique(array_values($result));
        sort($result);
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
    private function dumpDivisionWithId(OutputInterface $output, $name, $filename)
    {
        $query = Query::create($name);
        $json_filename = 'dump_' . $name . '_division.json';
        if (file_exists($json_filename)) {
            $result = $this->read($output, $json_filename);
        } else {
            $result = $this->divisions($output, $query, $filename, 'dump_' . $name . '.json');
        }
        if (count($result) > 0) {
            $result = $this->divisionsWithId($output, $query, $result);
            $this->write($output, $filename, $result);
        }
    }

    /**
     * @param OutputInterface $output
     * @param FileQuery $query
     * @param string[] $divisions
     * @return array
     */
    private function divisionsWithId(OutputInterface $output, FileQuery $query, $divisions)
    {
        $result = [];
        $output->writeln("<info>translate division to division_id:</info>");
        $this->progress = new ProgressBar($output, count($divisions));
        $this->progress->start();
        $n = 0;
        $time = Query::time();
        foreach ($divisions as $division) {
            $result[$division] = $query->idByDivision($division);
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

    private function dumpCount(OutputInterface $output, $name, $filename)
    {
        $result = [];
        $query = Query::create($name);
        if (count($query) > 0) {
            $output->writeln("<info>dump {$filename}:</info>");
            $this->progress = new ProgressBar($output, count($query));
            $this->progress->start();
            $n = 0;
            $time = Query::time();
            $last = -1;
            foreach ($query as $ip => $division) {
                if (is_integer($division)) {
                    $id = $division;
                    $division = $query->divisionById($id);
                } else {
                    $id = $query->idByDivision($division);
                }
                if ($id === null) {
                    die(long2ip($ip));
                }
                $count = $ip - $last;
                $last = $ip;
                $result[$id]['id'] = $id;
                $result[$id]['division'] = empty($id) ? '' : $division;
                @$result[$id]['records'] += 1;    // 纪录数
                @$result[$id]['count'] += $count;   // IP 数
                if ($id > 100000) { // 中国
                    @$result[1]['records'] += 1;
                    @$result[1]['children_records'] += 1;
                    @$result[1]['count'] += $count;
                    @$result[1]['children_count'] += $count;
                    $province = intval($id / 10000) * 10000;
                    if ($province != $id) {
                        @$result[$province]['records'] += 1;
                        @$result[$province]['children_records'] += 1;
                        @$result[$province]['count'] += $count;
                        @$result[$province]['children_count'] += $count;
                    }
                }
                $n++;
                if ($time < Query::time()) {
                    $this->progress->setProgress($n);
                    $time = Query::time();
                }
            }
            $this->progress->finish();
            $output->writeln('<info> completed!</info>');
        }
        ksort($result);
        $result = array_map(function ($data) {
            $result = [
                'id' => $data['id'],
                'division' => $data['division'],
                'records' => $data['records'],
                'count' => $data['count'],
            ];
            if (isset($data['children_records'])) {
                $result['self']['records'] = $data['records'] - $data['children_records'];
                $result['children']['records'] = $data['children_records'];
            }
            if (isset($data['children_count'])) {
                $result['self']['count'] = $data['count'] - $data['children_count'];
                $result['children']['count'] = $data['children_count'];
            }
            return $result;
        }, array_values($result));
        if (count($result) > 0) {
            $this->write($output, $filename, $result);
        }
    }
}

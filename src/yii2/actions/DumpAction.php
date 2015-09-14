<?php
/**
 * DumpAction.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\yii2\actions;

use larryli\ipv4\query\FileQuery;
use larryli\ipv4\query\Query;
use Yii;
use yii\helpers\Console;

/**
 * Class DumpAction
 * @package larryli\ipv4\yii2\actions
 */
class DumpAction extends Action
{
    /**
     * dump ip database
     *
     * @param string $type division or division_id
     *
     * @throws \Exception
     */
    public function run($type = 'default')
    {
        $this->stdout("dump {$type}:\n", Console::FG_GREEN);
        switch ($type) {
            case 'default':
                foreach ($this->ipv4->providers as $name => $provider) {
                    $this->dumpDefault($name, 'dump_' . $name . '.json');
                }
                break;
            case 'division':
                foreach ($this->ipv4->providers as $name => $provider) {
                    $this->dumpDivision($name, 'dump_' . $name . '_division.json');
                }
                break;
            case 'division_id':
                foreach ($this->ipv4->providers as $name => $provider) {
                    if (empty($provider)) {
                        $this->dumpDivisionWithId($name, 'dump_' . $name . '_division_id.json');
                    }
                }
                break;
            default:
                $this->stderr("Unknown type \"{$type}\".\n", Console::FG_GREY, Console::BG_RED);
                break;
        }
    }

    /**
     * @param string $name
     * @param string $filename
     * @throws \Exception
     */
    private function dumpDefault($name, $filename)
    {
        $query = $this->ipv4->createQuery($name);
        $result = $this->dump($query, $filename);
        if (count($result) > 0) {
            $this->write($filename, $result);
        }
    }

    /**
     * @param Query $query
     * @param $filename
     * @return array
     */
    private function dump(Query $query, $filename)
    {
        $result = [];
        if (count($query) > 0) {
            $this->stdout("dump {$filename}:\n", Console::FG_GREEN);
            $total = count($query);
            Console::startProgress(0, $total);
            $n = 0;
            $time = Query::time();
            foreach ($query as $ip => $division) {
                if (is_integer($division)) {
                    $division = $query->divisionById($division);
                }
                $result[long2ip($ip)] = $division;
                $n++;
                if ($time < Query::time()) {
                    Console::updateProgress($n, $total);
                    $time = Query::time();
                }
            }
            Console::updateProgress($total, $total);
            Console::endProgress();
            $this->stdout(" completed!\n", Console::FG_GREEN);
        }
        return $result;
    }

    /**
     * @param string $filename
     * @param string[] $result
     */
    private function write($filename, $result)
    {
        $this->stdout("write {$filename}:", Console::FG_GREEN);
        file_put_contents($filename, json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        $this->stdout(" completed!\n", Console::FG_GREEN);
    }

    /**
     * @param string $name
     * @param string $filename
     * @throws \Exception
     */
    private function dumpDivision($name, $filename)
    {
        $query = $this->ipv4->createQuery($name);
        $result = $this->divisions($query, $filename, 'dump_' . $name . '.json');
        if (count($result) > 0) {
            $result = array_unique(array_values($result));
            sort($result);
            $this->write($filename, $result);
        }
    }

    /**
     * @param Query $query
     * @param $filename
     * @param $json_filename
     * @return array|\string[]
     */
    private function divisions(Query $query, $filename, $json_filename)
    {
        if (file_exists($json_filename)) {
            $result = $this->read($json_filename);
        } else {
            $result = $this->dump($query, $filename);
        }
        $result = array_unique(array_values($result));
        sort($result);
        return $result;
    }

    /**
     * @param string $filename
     * @return string[] $result
     */
    private function read($filename)
    {
        $this->stdout("read {$filename}:", Console::FG_GREEN);
        $result = json_decode(file_get_contents($filename), true);
        $this->stdout(" completed!\n", Console::FG_GREEN);
        return $result;
    }

    /**
     * @param string $name
     * @param string $filename
     * @throws \Exception
     */
    private function dumpDivisionWithId($name, $filename)
    {
        $query = $this->ipv4->createQuery($name);
        $json_filename = 'dump_' . $name . '_division.json';
        if (file_exists($json_filename)) {
            $result = $this->read($json_filename);
        } else {
            $result = $this->divisions($query, $filename, 'dump_' . $name . '.json');
        }
        if (count($result) > 0) {
            $result = $this->divisionsWithId($query, $result);
            $this->write($filename, $result);
        }
    }

    /**
     * @param FileQuery $query
     * @param string[] $divisions
     * @return array
     */
    private function divisionsWithId(FileQuery $query, $divisions)
    {
        $result = [];
        $this->stdout("translate division to division_id:\n", Console::FG_GREEN);
        $total = count($divisions);
        Console::startProgress(0, $total);
        $n = 0;
        $time = Query::time();
        foreach ($divisions as $division) {
            $result[$division] = $query->idByDivision($division);
            $n++;
            if ($time < Query::time()) {
                Console::updateProgress($n, $total);
                $time = Query::time();
            }
        }
        Console::updateProgress($total, $total);
        Console::endProgress();
        $this->stdout(" completed!\n", Console::FG_GREEN);
        return $result;
    }
}

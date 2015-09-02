<?php
/**
 * Command.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\IPv4\Command;

use larryli\IPv4\IPDB\MonIPDB;
use larryli\IPv4\IPDB\QQWry;
use Symfony\Component\Console\Command\Command as BaseCommand;

class Command extends BaseCommand
{
    protected $runtime = '';

    protected function getRuntime($filename = '')
    {
        return $this->runtime . '/' . $filename;
    }

    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->runtime = realpath(dirname(__DIR__) . '/../runtime');
        if (empty($this->runtime)) {
            throw new \Exception('larryli\\IPv4 runtime must not empty!');
        }
    }

    protected function newIPDB($name)
    {
        switch ($name) {
            case '17monipdb':
                return new MonIPDB($this->getRuntime('17monipdb.dat'));
            case 'qqwry':
                return new QQWry($this->getRuntime('qqwry.dat'));
            default:
                throw new \Exception("Unknown IPDB name \"{$name}\"");
        }
    }
}
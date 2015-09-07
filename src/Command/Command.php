<?php
/**
 * Command.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\Command;

/**
 * Class Command
 * @package larryli\ipv4\Command
 */
class Command extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var string
     */
    protected $runtime = '';

    /**
     * @param string $filename
     * @return string
     */
    protected function getRuntime($filename = '')
    {
        return $this->runtime . '/' . $filename;
    }

    /**
     * @param null $name
     * @throws \Exception
     */
    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->runtime = realpath(dirname(dirname(__DIR__)) . '/runtime');
        if (empty($this->runtime)) {
            throw new \Exception('larryli\\ipv4 runtime must not empty!');
        }
    }

    /**
     * @param $name
     * @return \larryli\ipv4\Query\ChinaQuery|\larryli\ipv4\Query\FullQuery|\larryli\ipv4\Query\MiniQuery|\larryli\ipv4\Query\MonIPDBQuery|\larryli\ipv4\Query\QQWryQuery|\larryli\ipv4\Query\WorldQuery
     * @throws \Exception
     */
    protected function newQuery($name)
    {
        switch ($name) {
            case '17monipdb':
                return new \larryli\ipv4\Query\MonIPDBQuery($this->getRuntime('17monipdb.dat'));
            case 'qqwry':
                return new \larryli\ipv4\Query\QQWryQuery($this->getRuntime('qqwry.dat'));
            case 'full':
                return new \larryli\ipv4\Query\FullQuery();
            case 'mini':
                return new \larryli\ipv4\Query\MiniQuery();
            case 'china':
                return new \larryli\ipv4\Query\ChinaQuery();
            case 'world':
                return new \larryli\ipv4\Query\WorldQuery();
            default:
                throw new \Exception("Unknown Query name \"{$name}\"");
        }
    }
}
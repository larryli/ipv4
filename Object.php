<?php
/**
 * Object.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4;


/**
 * Class Object
 * @package larryli\ipv4
 */
class Object
{
    /**
     * @return string class name
     */
    public static function className()
    {
        return get_called_class();
    }

    /**
     * @param mixed $object
     * @return bool if the object is of this class or parents
     */
    public static function is_a($object)
    {
        return is_a($object, get_called_class());
    }
}
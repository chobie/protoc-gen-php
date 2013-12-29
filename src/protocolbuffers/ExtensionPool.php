<?php
/*
 * This file is part of the protoc-gen-php package.
 *
 * (c) Shuhei Tanuma <shuhei.tanuma@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace protocolbuffers;

class ExtensionPool
{
    protected static $pool = array();

    public static function register($name, $number, $descriptor)
    {
        $name = ltrim($name, ".");
        self::$pool[$name][$number] = $descriptor;
    }

    public static function has($name, $number)
    {
        $name = ltrim($name, ".");
        if (isset(self::$pool[$name][$number])) {
            return true;
        } else {
            return false;
        }
    }

    public static function get($name, $number)
    {
        $name = ltrim($name, ".");
        if (isset(self::$pool[$name][$number])) {
            return self::$pool[$name][$number];
        } else {
            error_log(var_export(array_keys(self::$pool), true));
            throw new \InvalidArgumentException(sprintf("%s:%s does not find", $name, $number));
        }
    }

}

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

class MessagePool
{
    protected static $pool = array();

    public static function register($name, $descriptor)
    {
        self::$pool[$name] = $descriptor;
    }

    public static function get($name)
    {
        if (isset(self::$pool[$name])) {
            return self::$pool[$name];
        }
    }

}

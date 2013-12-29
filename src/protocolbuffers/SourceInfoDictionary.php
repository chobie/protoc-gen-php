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

class SourceInfoDictionary
{
    protected static $dictionary = array();

    public static function register($dictionary)
    {
        self::$dictionary = $dictionary;
    }

    public static function get($filename, $message, $name)
    {
        if (isset(self::$dictionary[$filename][$message][$name])) {
            return self::$dictionary[$filename][$message][$name];
        }
    }

}

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

class StringStream
{
    protected $buffer = "";

    public function __construct($string = "")
    {
        $this->buffer = $string;
    }

    public function append($string)
    {
        $this->buffer .= $string;
    }

    public function assign($message)
    {
        $this->buffer = $message;
    }

    public function __toString()
    {
        return $this->buffer;
    }
}
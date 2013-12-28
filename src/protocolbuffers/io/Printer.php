<?php
/*
 * This file is part of the protoc-gen-php package.
 *
 * (c) Shuhei Tanuma <shuhei.tanuma@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace protocolbuffers\io;

class Printer
{
    protected $replace;
    protected $level = 0;
    protected $indent_char = '  ';
    protected $stream;

    protected $next;

    public function __construct($stream, $replace)
    {
        $this->stream = $stream;
        $this->replace = $replace;
    }


    public function indent()
    {
        $this->level++;
    }

    public function outdent()
    {
        $this->level--;
    }

    public function put($message/* $args */)
    {
        $args = func_get_args();
        array_shift($args);

        $key = "";
        $value = "";
        $tmp = array();
        if (count($args)) {
            for ($i = 0; $i < count($args); $i++) {
                if ($i % 2 == 0) {
                    $key = $args[$i];
                } else {
                    $value = $args[$i];

                    $tmp[$key] = $value;
                    unset($key);
                    unset($value);
                }
            }
            foreach ($tmp as $key => $value) {
                $message = str_replace(sprintf("%s%s%s", $this->replace, $key, $this->replace), $value, $message);
            }
        }

        if ($this->next) {
            $this->stream->write(str_repeat($this->indent_char, $this->level));
        }

        if (preg_match('/\n$/m', $message)) {
            $this->next = true;
        } else {
            $this->next = false;
        }
        $this->stream->write($message);
    }
}
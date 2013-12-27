<?php
namespace protocolbuffers\io;

class Printer
{
    protected $replace;
    protected $level = 0;
    protected $indent = 2;
    protected $stream;

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

        $this->stream->write(str_repeat(" ", $this->level * $this->indent));
        $this->stream->write($message);
    }
}
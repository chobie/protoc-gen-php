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

use google\protobuf\compiler\CodeGeneratorResponse;

class GeneratorContext
{
    protected $response;
    protected $contexts = array();

    public function __construct(CodeGeneratorResponse $response)
    {
        $this->response = $response;
    }

    public function hasOpened($name)
    {
        if (isset($this->contexts[$name])) {
            return true;
        } else {
            return false;
        }
    }

    public function open($name)
    {
        $file = new \google\protobuf\compiler\CodeGeneratorResponse\File();
        $stream  = new StringStream();
        $file->setContent($stream);
        $file->setName($name);
        $this->response->appendFile($file);

        $stream = new ZeroCopyOutputStream($stream);
        $this->contexts[$name] = $stream;

        return $stream;
    }

    public function openForInsert($name, $insertion_point)
    {
        $file = new \google\protobuf\compiler\CodeGeneratorResponse\File();
        $stream  = new StringStream();
        $file->setName($name);
        $file->setContent($stream);
        $file->setInsertionPoint($insertion_point);
        $this->response->appendFile($file);

        $stream = new ZeroCopyOutputStream($stream);
        $this->contexts[$name] = $stream;

        return $stream;
    }
}

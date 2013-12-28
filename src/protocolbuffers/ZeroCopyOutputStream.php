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

class ZeroCopyOutputStream
{
    protected $stream;

    public function __construct($stream)
    {
        $this->stream = $stream;
    }

    public function write($message)
    {
        $this->stream->append($message);
    }
}
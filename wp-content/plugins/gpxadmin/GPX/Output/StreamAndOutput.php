<?php

namespace GPX\Output;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

class StreamAndOutput extends StreamOutput implements OutputInterface
{
    protected function doWrite(string $message, bool $newline)
    {
        echo $message;

        if ($newline) {
            echo \PHP_EOL;
        }

        parent::doWrite($message, $newline);
    }
}

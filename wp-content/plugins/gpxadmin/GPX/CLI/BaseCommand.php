<?php

namespace GPX\CLI;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class BaseCommand extends Command {
    protected $io;

    public function io( $input, $output ): SymfonyStyle {
        if ( ! $this->io ) {
            $this->io = new SymfonyStyle( $input, $output );
        }

        return $this->io;
    }
}
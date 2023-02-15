<?php

namespace GPX\CLI;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class BaseCommand extends Command {
    protected ?SymfonyStyle $io;

    public function io( $input, $output ): SymfonyStyle {
        return new SymfonyStyle( $input, $output );
    }
}

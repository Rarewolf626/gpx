<?php

namespace GPX\CLI\Cache;

use GPX\CLI\BaseCommand;
use Symfony\Component\Finder\Finder;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearViewCacheCommand extends BaseCommand {

    protected function configure(): void {
        $this->setName( 'cache:clear:view' );
        $this->setDescription( 'Clear compiled view files' );
    }

    protected function execute( InputInterface $input, OutputInterface $output ): int {
        $io = $this->io( $input, $output );
        $io->title( 'Clear view cache' );

        /** @var Filesystem $filesystem */
        $filesystem = gpx( 'Illuminate\Filesystem\Filesystem' );
        $count = 0;
        $finder = Finder::create()->in( WP_CONTENT_DIR . '/gpx-cache/view' )->files()->name( '*.php' );
        foreach ( $finder as $file ) {
            if ( $output->isVerbose() ) {
                $output->writeln( $file->getRealPath() );
            }

            $filesystem->delete( $file->getRealPath() );
            $count ++;
        }
        $io->success( "Removed {$count} compiled view files" );

        return static::SUCCESS;
    }
}

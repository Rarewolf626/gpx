<?php

namespace GPX\CLI\CustomRequests;

use GPX\CLI\BaseCommand;
use GPX\Model\CustomRequest;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DisableExpiredCustomRequestsCommand extends BaseCommand {

    protected bool $debug = false;

    protected function configure(): void {
        $this->setName( 'request:expired' );
        $this->setDescription( 'Disabled expired custom requests' );
        $this->setHelp( 'Checks active custom requests and disabled any with checkin dates in the past' );
        $this->addOption( 'debug',
                          'd',
                          InputOption::VALUE_NONE,
                          'In debug mode no updates are made' );
    }

    protected function execute( InputInterface $input, OutputInterface $output ): int {
        $this->io = new SymfonyStyle( $input, $output );
        $this->io->title( 'Pull expired custom requests' );
        $this->io->writeln(date('m/d/Y g:i:s A'));
        $this->debug = (bool) $input->getOption( 'debug' );
        if ( $this->debug ) {
            $this->io->warning( 'Currently in debug mode. Any updates will not be persisted.' );
        }


        $disabled = CustomRequest::active()->expired()->get();
        if ( $disabled->isEmpty() ) {
            $this->io->success( 'No expired custom requests to update' );

            return Command::SUCCESS;
        }
        $this->io->table( [ 'ID', 'Checkin', 'Checkin2', 'Resort/Region', 'email' ],
                    $disabled->map( fn( CustomRequest $request ) => [
                        $request->id,
                        $request->checkIn->format( 'm/d/Y' ),
                        $request->checkIn2 ? $request->checkIn2->format( 'm/d/Y' ) : $request->checkIn->clone()->addWeek()->format( 'm/d/Y' ),
                        $request->resort ?? ( $request->city . ', ' . $request->region ),
                        $request->email,
                    ] )->toArray() );
        if ( ! $this->debug ) {
            CustomRequest::whereIn( 'id', $disabled->pluck( 'id' ) )->update( [ 'active' => false ] );
        }
        $this->io->success( sprintf( 'Disabled %d expired custom requests', $disabled->count() ) );

        return Command::SUCCESS;
    }
}

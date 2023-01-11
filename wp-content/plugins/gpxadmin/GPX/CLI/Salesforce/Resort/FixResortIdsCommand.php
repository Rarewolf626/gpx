<?php

namespace GPX\CLI\Salesforce\Resort;

use GPX\CLI\BaseCommand;
use Psr\Log\LoggerInterface;
use GPX\Api\Salesforce\Salesforce;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FixResortIdsCommand extends BaseCommand {
    protected Salesforce $sf;
    protected LoggerInterface $logger;

    public function __construct( Salesforce $sf ) {
        $this->sf = $sf;
        $this->logger = gpx_logger();
        parent::__construct();
    }

    protected function configure(): void {
        $this->setName( 'sf:resort:fix-ids' );
        $this->setDescription( 'Updates salesforce resort ids' );
    }

    protected function execute( InputInterface $input, OutputInterface $output ): int {
        $io = $this->io( $input, $output );
        $io->title( 'Pull resorts from salesforce' );
        $query = "SELECT Id, Name from Resort__c";
        $resorts = $this->sf->query( $query );
        foreach ( $resorts as $row ) {
            $io->section( $row->Name );
            dump($row);
            $resort = \DB::table( 'wp_resorts' )
                        ->select('id', 'gprID', 'ResortName')
                         ->where( 'ResortName', '=', $row->Name )
                         ->first();
            if ( ! $resort ) {
                $io->warning('Resort was not found');
                continue;
            }
            if($resort->gprID === $row->Id){
                $io->success('Resort id was already matching');
                continue;
            }
            \DB::table( 'wp_resorts' )
               ->where( 'ResortName', '=', $row->Name )
               ->update( [ 'gprID' => $row->Id ] );
            $this->logger->debug(sprintf('Fixed salesforce id for resort', [
                'resort' => $row->Name,
                'old' => $resort->gprID,
                'new' => $row->Id,
            ]));
            $io->success(sprintf('Updated resort from %s id to %s',$resort->gprID, $row->Id));
        }

        return Command::SUCCESS;
    }
}

<?php

namespace GPX\CLI\Salesforce\Owner;

use GPX\CLI\BaseCommand;
use Psr\Log\LoggerInterface;
use GPX\Import\OwnerImporter;
use GPX\Api\Salesforce\Salesforce;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportOwnersFromSalesforceCommand extends BaseCommand {
    protected Salesforce $sf;
    protected OwnerImporter $repository;
    protected LoggerInterface $logger;

    public function __construct( Salesforce $sf, OwnerImporter $repository ) {
        $this->sf         = $sf;
        $this->repository = $repository;
        $this->logger = gpx_logger();
        parent::__construct();
    }

    protected function configure(): void {
        $this->setName( 'sf:owner:import' );
        $this->setDescription( 'Import owners from salesforce' );
        $this->setHelp( 'Import owners from salesforce' );
        $this->addOption( 'limit', 'l', InputOption::VALUE_REQUIRED, 'Max number of owners to import', 100 );
    }

    protected function execute( InputInterface $input, OutputInterface $output ): int {
        $io = $this->io( $input, $output );
        $io->title( 'Import owners from salesforce' );
        $limit  = $input->getOption( 'limit' ) ?: 100;
        $owners = $this->sf->owner->new_owners( $limit, 0, true );
        $io->info( sprintf( '%d owners to import', count( $owners ) ) );
        foreach ( $owners as $sfOwner ) {
            $io->section( $sfOwner->Name );
            try {
                $owner = $this->repository->import_from_sf( $sfOwner );
                $io->success('Imported Owner');
                $io->table(['SF Name', 'WP User ID','Email', 'Name'], [[$sfOwner->Name, $owner->user_id, $owner->SPI_Email__c, $owner->SPI_Owner_Name_1st__c]]);
            } catch ( \Exception $e ) {
                $this->logger->error("Failed to import owner from salesforce", [
                    'exception' => $e,
                    'sfObject' => $sfOwner,
                ]);
                $io->error( $e );
            }
        }


        return Command::SUCCESS;
    }
}

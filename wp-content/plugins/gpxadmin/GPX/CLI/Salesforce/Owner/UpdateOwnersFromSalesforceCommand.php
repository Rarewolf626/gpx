<?php

namespace GPX\CLI\Salesforce\Owner;

use GPX\CLI\BaseCommand;
use Psr\Log\LoggerInterface;
use Illuminate\Support\Carbon;
use GPX\Api\Salesforce\Salesforce;
use GPX\Repository\OwnerRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateOwnersFromSalesforceCommand extends BaseCommand {
    protected Salesforce $sf;
    protected OwnerRepository $repository;
    protected LoggerInterface $logger;

    public function __construct( Salesforce $sf, OwnerRepository $repository ) {
        $this->sf         = $sf;
        $this->repository = $repository;
        $this->logger     = gpx_logger();
        parent::__construct();
    }

    protected function configure(): void {
        $this->setName( 'sf:owner:update' );
        $this->setDescription( 'Update owners from salesforce' );
        $this->setHelp( 'Update owners from salesforce' );
        $this->addOption( 'limit', 'l', InputOption::VALUE_REQUIRED, 'Max number of owners to import' );
        $this->addOption( 'days', 'd', InputOption::VALUE_REQUIRED, 'Check owners updated in the last n days.' );
    }

    protected function execute( InputInterface $input, OutputInterface $output ): int {
        $io = $this->io( $input, $output );
        $io->title( 'Update owners from salesforce' );
        $now  = time();
        $days = (int) $input->getOption( 'days' ) ?: null;
        if ( $days ) {
            $last_checked = Carbon::now()->subDays( $days )->startOfDay();
        } else {
            $last_checked = (int) get_option( 'gpx_sf_owner_update_last_checked', 0 );
            $last_checked = $last_checked ? Carbon::createFromTimestamp( $last_checked ) : Carbon::now()->subDay()->startOfDay();
        }
        $io->info( sprintf( 'Pull owners updated after %s', $last_checked->format( 'm/d/Y h:i:s A' ) ) );
        $limit  = $input->getOption( 'limit' ) ?: null;
        $owners = $this->sf->owner->updated_owners( $last_checked, $limit );
        $io->info( sprintf( '%d owners to update', count( $owners ) ) );
        foreach ( $owners as $sfOwner ) {
            $io->section( $sfOwner->Id );
            try {
                $owner = $this->repository->import_from_sf( $sfOwner );
                $io->success( 'Imported Owner' );
                $io->table( [ 'SF Id', 'SF Name', 'WP User ID', 'Email', 'Name', '# Intervals' ],
                            [
                                [
                                    $sfOwner->Id,
                                    $sfOwner->Name,
                                    $owner->user_id,
                                    $owner->SPI_Email__c,
                                    $owner->SPI_Owner_Name_1st__c,
                                    count( $sfOwner->intervals ),
                                ],
                            ] );
            } catch ( \Exception $e ) {
                $this->logger->error( "Failed to update owner from salesforce", [
                    'exception' => $e,
                    'sfObject'  => $sfOwner,
                ] );
                $io->error( $e );
            }
        }
        if ( ! $days && ! $limit ) {
            update_option( 'gpx_sf_owner_update_last_checked', $now, false );
        }

        return Command::SUCCESS;
    }
}

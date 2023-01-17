<?php

namespace GPX\CLI\Salesforce\Owner;

use Faker\Generator;
use GPX\CLI\BaseCommand;
use Illuminate\Support\Arr;
use Psr\Log\LoggerInterface;
use GPX\Api\Salesforce\Salesforce;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateOwnerUpdatesInSalesforceCommand extends BaseCommand {
    protected Salesforce $sf;
    protected LoggerInterface $logger;
    protected Generator $faker;
    /**
     * Default array of owner names in salesforce to update
     *
     * @var array|int[]
     */
    private array $owners = [ 16186,10821,994785620,99472142,407211741 ];

    public function __construct( Salesforce $sf ) {
        $this->sf     = $sf;
        $this->logger = gpx_logger();
        $this->faker  = \Faker\Factory::create( 'en_US' );
        parent::__construct();
    }

    protected function configure(): void {
        $this->setName( 'sf:owner:fake-updates' );
        $this->setDescription( 'Updates owners from salesforce with some random data for testing' );
        $this->setHelp( 'Updates owners from salesforce with some random data for testing' );
        $this->addOption( 'owners',
                          'o',
                          InputOption::VALUE_REQUIRED,
                          'Comma-separated list of salesforce owner names to update',
                          implode( ',', $this->owners ) );

        $this->addOption( 'gpx',
                          'g',
                          InputOption::VALUE_REQUIRED,
                          'Comma-separated list of owner gpx vest ids to update',
                          null );
    }

    protected function execute( InputInterface $input, OutputInterface $output ): int {
        $io = $this->io( $input, $output );
        $io->title( 'Import owners from salesforce' );
        $gpxids = $input->getOption( 'gpx' );
        if($gpxids){
            $ids = array_filter( Arr::wrap( explode( ',', $gpxids ) ) );
        } else {
            $ids = $input->getOption( 'owners' ) ?: implode( ',', $this->owners );
            $ids = array_filter( array_map( 'intval', Arr::wrap( explode( ',', $ids ) ) ) );
        }
        $io->info( sprintf( 'Owners to update: %s', implode( ',', $ids ) ) );
        $owners = $this->sf->owner->get_owners_by_id( $ids, $gpxids ? 'gpx' : 'sf' );
        foreach ( $owners as $sfOwner ) {
            $io->section( $sfOwner->Id );
            try {
                $current         = [
                    'Name'               => $sfOwner->Name,
                    'SPI_First_Name__c'  => $sfOwner->SPI_First_Name__c,
                    'SPI_Last_Name__c'   => $sfOwner->SPI_Last_Name__c . ' [TEST]',
                    'SPI_Email__c'       => $sfOwner->SPI_Email__c,
                    'SPI_Home_Phone__c'  => $sfOwner->SPI_Home_Phone__c,
                    'SPI_Work_Phone__c'  => $sfOwner->SPI_Work_Phone__c,
                    'SPI_Street__c'      => $sfOwner->SPI_Street__c,
                    'SPI_City__c'        => $sfOwner->SPI_City__c,
                    'SPI_State__c'       => $sfOwner->SPI_State__c,
                    'SPI_Zip_Code__c'    => $sfOwner->SPI_Zip_Code__c,
                    'SPI_Country__c'     => $sfOwner->SPI_Country__c,
                    'GPX_Member_VEST__c' => $sfOwner->GPX_Member_VEST__c,
                ];
                $new             = [
                    'Name'               => $sfOwner->Name,
                    'SPI_First_Name__c'  => $this->faker->firstName,
                    'SPI_Last_Name__c'   => $this->faker->lastName,
                    'SPI_Email__c'       => $this->faker->safeEmail,
                    'SPI_Home_Phone__c'  => gpx_format_phone( $this->faker->phoneNumber ),
                    'SPI_Work_Phone__c'  => gpx_format_phone( $this->faker->phoneNumber ),
                    'SPI_Street__c'      => $this->faker->streetAddress,
                    'SPI_City__c'        => $this->faker->city,
                    'SPI_State__c'       => $this->faker->stateAbbr,
                    'SPI_Zip_Code__c'    => $this->faker->postcode,
                    'SPI_Country__c'     => 'USA',
                    'GPX_Member_VEST__c' => $sfOwner->GPX_Member_VEST__c,
                ];
                $sfOwner->fields = $new;
                $this->sf->gpxUpsert( 'Name', [ $sfOwner ] );
                $io->success( 'Updated Owner' );
                $io->horizontalTable( [ array_keys( $current ) ], [ $current, $new ] );
            } catch ( \Exception $e ) {
                $this->logger->error( "Failed to update owner in salesforce", [
                    'exception' => $e,
                    'sfObject'  => $sfOwner,
                ] );
                $io->error( $e );
            }
        }


        return Command::SUCCESS;
    }
}

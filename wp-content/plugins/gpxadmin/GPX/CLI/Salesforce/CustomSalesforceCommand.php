<?php

namespace GPX\CLI\Salesforce;

use GPX\CLI\BaseCommand;
use Psr\Log\LoggerInterface;
use GPX\Import\OwnerImporter;
use Illuminate\Support\Carbon;
use GPX\Api\Salesforce\Salesforce;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class CustomSalesforceCommand extends BaseCommand {
    protected Salesforce $sf;

    public function __construct( Salesforce $sf ) {
        $this->sf = $sf;
        parent::__construct();
    }

    protected function configure(): void {
        $this->setName( 'sf:query' );
        $this->setDescription( 'Run custom salesforce query' );
    }

    protected function execute( InputInterface $input, OutputInterface $output ): int {
        $io = $this->io( $input, $output );
        $io->title( 'Run custom salesforce query' );
        $helper   = $this->getHelper( 'question' );
        $question = new Question( 'Please enter the query: (CTR-D to confirm)' . PHP_EOL, '' );
        $question->setMultiline( true );

        $continue = new ConfirmationQuestion( 'Enter another query?', false );
        do {
            do {
                $query = $helper->ask( $input, $output, $question );
            } while ( ! $query );
            $io->newLine();
            try {
                $results = $this->sf->query( $query );
                dump( $results );
            } catch ( \Exception $e ) {
                $io->error( $e );
            }
            $io->newLine(2);
        } while ( $helper->ask( $input, $output, $continue ) );

        return Command::SUCCESS;
    }
}

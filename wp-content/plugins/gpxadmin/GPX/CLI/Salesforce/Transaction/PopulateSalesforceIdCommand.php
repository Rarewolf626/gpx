<?php

namespace GPX\CLI\Salesforce\Transaction;

use GPX\CLI\BaseCommand;
use GPX\Model\Transaction;
use GPX\Api\Salesforce\Salesforce;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PopulateSalesforceIdCommand extends BaseCommand {
    protected Salesforce $sf;

    public function __construct(Salesforce $sf) {
        $this->sf = $sf;
        parent::__construct();
    }

    protected function configure(): void {
        $this->setName('sf:transaction:populate');
        $this->setDescription('Updates salesforce ids for transactions missing them');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $io = $this->io($input, $output);
        $io->title('Populate salesforce ids for transactions');

        global $wpdb;
        $sql = "SELECT `id` FROM `wp_gpxTransactions` WHERE `sfid` = '0' AND `sfid` IS NOT NULL ORDER BY `id` DESC";
        $transactions = $wpdb->get_results($sql);
        foreach ($transactions as $transaction){
            $io->section('Transaction ' . $transaction->id);
            $sfql = $wpdb->prepare("SELECT Id FROM GPX_Transaction__c WHERE GPXTransaction__c = %d", $transaction->id);
            $result = $this->sf->query($sfql);
            if (empty($result)) {
                $io->warning('Transaction not found in salesforce.');
            } else {
                $wpdb->update('wp_gpxTransactions', ['sfid' => $result[0]->Id], ['id' => $transaction->id]);
                $io->success(sprintf('Transaction %d updated with salesforce id %s.', $transaction->id, $result[0]->Id));
            }
        }

        return Command::SUCCESS;
    }
}

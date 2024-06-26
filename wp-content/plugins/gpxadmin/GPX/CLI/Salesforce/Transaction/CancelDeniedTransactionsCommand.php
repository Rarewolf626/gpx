<?php

namespace GPX\CLI\Salesforce\Transaction;

use DB;
use SObject;
use GPX\Model\Credit;
use GPX\CLI\BaseCommand;
use GPX\Model\Transaction;
use Illuminate\Support\Carbon;
use GPX\Api\Salesforce\Salesforce;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CancelDeniedTransactionsCommand extends BaseCommand {
    protected Salesforce $sf;

    public function __construct(Salesforce $sf) {
        $this->sf = $sf;
        parent::__construct();
    }

    protected function configure(): void {
        $this->setName('sf:transaction:denied');
        $this->setDescription('Checks for transactions that have been denied in salesforce and cancels them in GPX');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $io = $this->io($input, $output);
        $io->title('Check for cancelled transactions in salesforce');

        $now = Carbon::now();
        $time = $now->clone()->subHour()->format('Y-m-d\TH:i:s\Z');
        $io->info(sprintf('Checking transactions updated since %s', $time));

        // Pull transactions in last hour from salesforce
        $query = sprintf(/** @lang sfquery */ "SELECT
            Id, Name, Status__c, GPX_Deposit__c
        FROM GPX_Transaction__c
        WHERE SystemModStamp > %s AND Status__c='Denied'", $time);
        $results = $this->sf->query($query) ?? [];
        if (empty($results)) {
            $io->success('No recently cancelled transactions found.');

            return Command::SUCCESS;
        }
        foreach ($results as $result) {
            $transaction = Transaction::cancelled(false)->find($result->fields->Name ?? 0);
            if (!$transaction) continue;
            $io->section('Cancelled Transaction ' . $transaction->id);
            $cupdate = $transaction->cancelledData ?? [];

            $cupdate[$now->timestamp] = [
                'userid' => 'system',
                'name' => 'system',
                'date' => $now->format('Y-m-d H:i:s'),
                'refunded' => '',
                'coupon' => '',
                'action' => 'refund',
                'amount' => '',
                'by' => 'system',
            ];

            $io->writeln("Mark transaction as cancelled in GPX");
            $transaction->update([
                'cancelled' => true,
                'cancelledDate' => $now->format('Y-m-d'),
                'cancelledData' => $cupdate,
            ]);

            $credit = Credit::where('record_id', '=', $result->fields->GPX_Deposit__c ?? 0);
            if (!$credit) continue;

            $io->writeln("Return used deposit.");
            $newCreditUsed = $credit->credit_used - 1;

            $modId = DB::table('wp_credit_modification')->insertGetId([
                'credit_id' => $result->fields->GPX_Deposit__c,
                'recorded_by' => '9999999',
                'data' => json_encode([
                    'type' => 'Deposit Denied',
                    'oldAmount' => $credit->credit_used,
                    'newAmount' => $newCreditUsed,
                    'date' => $now->format('Y-m-d'),
                ]),
            ]);

            $credit->update([
                'credit_used' => $newCreditUsed,
                'status' => 'Available',
                'modification_id' => $modId,
                'modified_date' => $now->format('Y-m-d'),
                'credit_action' => null,
            ]);

            $io->writeln("Push deposit changes to salesforce.");
            $sfFields = new SObject();
            $sfFields->type = 'GPX_Deposit__c';
            $sfFields->fields = [
                'GPX_Deposit_ID__c' => $credit->id,
                'Credits_Used__c' => $newCreditUsed,
                'Deposit_Status__c' => 'Approved',
            ];

            $this->sf->gpxUpsert('GPX_Deposit_ID__c', [$sfFields]);
        }

        return Command::SUCCESS;
    }
}

<?php

namespace GPX\CLI\Salesforce\Transaction;

use DB;
use SObject;
use Shiftfour;
use Money\Money;
use GPX\Model\Credit;
use GPX\Model\Interval;
use GPX\CLI\BaseCommand;
use GPX\Model\Transaction;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use GPX\Model\OwnerCreditCoupon;
use GPX\Api\Salesforce\Salesforce;
use GPX\Model\OwnerCreditCouponActivity;
use GPX\Repository\TransactionRepository;
use GPX\DataObject\Transaction\RefundResult;
use GPX\DataObject\Transaction\RefundRequest;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckDepositsCommand extends BaseCommand {
    protected Salesforce $sf;

    public function __construct(Salesforce $sf) {
        $this->sf = $sf;
        parent::__construct();
    }

    protected function configure(): void {
        $this->setName('sf:transaction:check-deposits');
        $this->setDescription('Checks salesforce deposits for changes');
        $this->addOption('deposit',
            'd',
            InputOption::VALUE_REQUIRED,
            'Comma-separated list of deposit ids to sync',
            null
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $deposit_ids = array_filter(array_map('intval', Arr::wrap(explode(',', $input->getOption('deposit') ?? ''))));

        if(empty($deposit_ids)){
            gpx_run_command(['command' => 'sf:transaction:denied'], $output);
        }

        global $wpdb;
        $io = $this->io($input, $output);
        $io->title('Check deposits for changes in salesforce');
        $now = Carbon::now();

        $query = /** @lang sfquery */
            "SELECT
            Id, Name, Account_Name__c, Check_In_Date__c, Credit_Extension_Date__c,
            Credits_Issued__c, Credits_Used__c, Deposit_Date__c, Deposit_Year__c, Expiration_Date__c,
            GPX_Deposit_ID__c, GPX_Member__c, Resort_Name__c, Resort_Unit_Week__c, Deposit_Status__c,
            Unit_Type__c, Coupon__c, Delete_this_Record__c
        FROM GPX_Deposit__c WHERE";
        if (!empty($deposit_ids)) {
            $placeholders = gpx_db_placeholders($deposit_ids, '%s');
            $io->info(sprintf('Checking deposits %s', implode(', ', $deposit_ids)));
            $query .= $wpdb->prepare(" GPX_Deposit_ID__c IN ({$placeholders})", $deposit_ids);
        } else {
            $time = $now->clone()->subHour()->format('Y-m-d\TH:i:s\Z');
            $io->info(sprintf('Checking deposits updated since %s', $time));
            $query .= sprintf(" SystemModStamp > %s", $time);
        }
        $query .= 'ORDER BY SystemModStamp ASC';
        $results = $this->sf->query($query) ?? [];
        if (empty($results)) {
            $io->success('No deposits needed to check.');

            return Command::SUCCESS;
        }
        foreach ($results as $result) {
            $value = $result->fields;
            $io->section('Deposit ' . $value->GPX_Deposit_ID__c);

            $deposit = Credit::find($value->GPX_Deposit_ID__c);
            if (!$deposit) {
                $io->error('Deposit not found in GPX');
                continue;
            }

            if ($value->Delete_this_Record__c == 'true') {
                $deposit->delete();
                $io->success('Deposit deleted from GPX');
                continue;
            }

            $ownerID = $deposit->owner_id;
            $newCreditUsed = (int) $deposit->credit_used - 1;
            $status = $deposit->status;

            $deposit->fill([
                'record_id' => $result->Id,
                'sf_name' => $result->Name,
                'credit_amount' => $value->Credits_Issued__c,
                'credit_used' => $value->Credits_Used__c,
                'credit_expiration_date' => $value->Expiration_Date__c,
                'resort_name' => stripslashes(str_replace("&", "&amp;", $value->Resort_Name__c)),
                'deposit_year' => $value->Deposit_Year__c,
                'unit_type' => $value->Unit_Type__c,
                'check_in_date' => $value->Check_In_Date__c,
                'extension_date' => $value->Credit_Extension_Date__c,
                'coupon' => $value->Coupon__c,
                'status' => $value->Deposit_Status__c,
            ]);
            if (!empty($value->Reservation__c)) {
                $deposit->reservation_number = $value->Reservation__c;
            }

            $deposit->save();
            $io->success('Deposit data updated in GPX');

            if ($status == $value->Deposit_Status__c) {
                $io->success("No further changes needed for this deposit");
                continue;
            }

            $io->writeln("Deposit status changed from $status to $value->Deposit_Status__c");
            if ($deposit->isApproved()) {
                $io->writeln('Deposit is approved, set last year banked to deposit year');
                Interval::where('userID', $deposit->owner_id)
                        ->where('unitweek', $value->Resort_Unit_Week__c)
                        ->where(fn($query) => $query
                            ->whereNull('Year_Last_Banked__c')
                            ->orWhere('Year_Last_Banked__c', '<', $value->Deposit_Year__c)
                        )
                        ->update(['Year_Last_Banked__c' => $value->Deposit_Year__c]);
            }

            $transaction = Transaction::select(['wp_gpxTransactions.*', 'wp_gpxDepostOnExchange.data as excd'])
                                      ->join('wp_credit', 'wp_credit.id', '=', 'wp_gpxTransactions.depositID')
                                      ->join('wp_gpxDepostOnExchange', 'wp_gpxDepostOnExchange.creditID', '=', 'wp_credit.id')
                                      ->where('wp_gpxTransactions.depositID', $value->GPX_Deposit_ID__c)
                                      ->first();
            if (!$transaction) {
                $transaction = Transaction::select(['wp_gpxTransactions.*', 'wp_gpxDepostOnExchange.data as excd'])
                                          ->join('wp_gpxDepostOnExchange', 'wp_gpxDepostOnExchange.id', '=', 'wp_gpxTransactions.depositID')
                                          ->where('wp_gpxDepostOnExchange.creditID', $value->GPX_Deposit_ID__c)
                                          ->first();
            }
            if (!$transaction) {
                $io->error('Transaction not found for deposit');
                continue;
            }

            $dexp = json_decode($transaction->excd);
            if ($dexp->GPX_Deposit_ID__c != $value->GPX_Deposit_ID__c) {
                $io->warning('Deposit id for deposit on exchange does not match, skip further modifications');
                continue;
            }

            if ($deposit->isApproved()) {
                $io->info('Deposit was approved');

                $io->info('Mark week as booked in salesforce');
                $sfFields = new SObject();
                $sfFields->type = 'GPX_Week__c';
                $sfFields->fields = [
                    'GpxWeekRefId__c' => $transaction->weekId,
                    'Status__c' => 'Booked',
                ];
                $this->sf->gpxUpsert('GpxWeekRefId__c', [$sfFields]);

                $io->info('Confirm transaction in salesforce');
                $sfData = [
                    'GPXTransaction__c' => $transaction->id,
                    'Reservation_Status__c' => 'Confirmed',
                ];
                if ($transaction->isCreditTransfer()) {
                    $sfData['Status__c'] = 'Approved';
                }

                $sfFields = new SObject();
                $sfFields->type = 'GPX_Transaction__c';
                $sfFields->fields = $sfData;
                $this->sf->gpxUpsert('GPXTransaction__c', [$sfFields]);

                $io->success('Set transaction as not cancelled in GPX');
                $transaction->update(['cancelled' => 0]);

                continue;
            }

            if ($transaction->cancelled || !$deposit->isDenied()) {
                continue;
            }

            $io->info('Deposit was denied, cancel transaction and refund payed amounts');

            $transData = $transaction->data;
            $canceledData = $transaction->cancelledData ?? [];
            $cancellations = collect(array_values($canceledData));

            $request = new RefundRequest([
                'cancel' => true,
                'amount' => round(($transData['Paid'] ?? 0.00) - $cancellations->where('action', '!=', 'credit')->sum('amount'), 2),
                'booking' => true,
                'booking_amount' => round(($transData['actWeekPrice'] ?? 0.00) - ($cancellations->where('type', '==', 'erFee')->sum('amount')), 2),
                'cpo' => true,
                'cpo_amount' => round(($transData['actcpoFee'] ?? 0.00) - ($cancellations->where('type', '==', 'cpofee')->sum('amount')), 2),
                'upgrade' => true,
                'upgrade_amount' => round(($transData['actupgradeFee'] ?? 0.00) - ($cancellations->where('type', '==', 'upgradefee')->sum('amount')), 2),
                'guest' => true,
                'guest_amount' => round(($transData['actguestFee'] ?? $transData['GuestFeeAmount'] ?? 0.00) - ($cancellations->where('type', '==', 'guestfeeamount')->sum('amount')), 2),
                'late' => true,
                'late_amount' => round(($transData['lateDepositFee'] ?? 0.00) - ($cancellations->where('type', '==', 'latedepositfee')->sum('amount')), 2),
                'third_party' => true,
                'third_party_amount' => round(($transData['thirdPartyDepositFee'] ?? 0.00) - ($cancellations->where('type', '==', 'thirdpartydepositfee')->sum('amount')), 2),
                'extension' => true,
                'extension_amount' => round(($transData['actextensionFee'] ?? 0.00) - ($cancellations->where('type', '==', 'creditextensionfee')->sum('amount')), 2),
                'tax' => true,
                'tax_amount' => round($transData['acttax'] ?? 0.00, 2),
            ]);
            $repository = TransactionRepository::instance();
            $refund = $repository->refundTransaction($transaction, $request, 'system');

            $io->info($refund->message);

            $repository->cancelTransaction($transaction);
            $io->success(sprintf('Transaction %s marked as cancelled.', $transaction->id));

            $modId = DB::table('wp_credit_modification')->insertGetId([
                'credit_id' => $value->GPX_Deposit_ID__c,
                'recorded_by' => '9999999',
                'data' => json_encode([
                    'type' => 'Deposit Denied',
                    'oldAmount' => $deposit->credit_used,
                    'newAmount' => $newCreditUsed,
                    'date' => $now->format('Y-m-d'),
                ]),
            ]);

            $sfFields = new SObject();
            $sfFields->type = 'GPX_Deposit__c';
            $sfFields->fields = [
                'GPX_Deposit_ID__c' => $value->GPX_Deposit_ID__c,
                'Credits_Used__c' => $newCreditUsed,
            ];
            $this->sf->gpxUpsert('GPX_Deposit_ID__c', [$sfFields]);
        }


        return Command::SUCCESS;
    }
}

<?php

namespace GPX\CLI\Salesforce\Transaction;

use GPX\CLI\BaseCommand;
use GPX\Model\Transaction;
use Illuminate\Support\Arr;
use Psr\Log\LoggerInterface;
use GPX\Api\Salesforce\Salesforce;
use GPX\Repository\TransactionRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PushMissingTransactionsCommand extends BaseCommand {
    protected Salesforce $sf;

    public function __construct(Salesforce $sf) {
        $this->sf = $sf;
        parent::__construct();
    }

    protected function configure(): void {
        $this->setName('sf:transaction:push');
        $this->setDescription('Pushes transactions missing from salesforce');
        $this->addOption('transaction',
            't',
            InputOption::VALUE_REQUIRED,
            'Comma-separated list of transaction ids to push',
            ''
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $io = $this->io($input, $output);
        $io->title('Push missing transactions to salesforce');

        $repository = TransactionRepository::instance();
        $ids = $input->getOption('transaction') ?? '';
        $ids = array_filter(array_map('intval', Arr::wrap(explode(',', $ids))));
        if (!empty($ids)) {
            $io->info(sprintf('Transactions to push: %s', implode(',', $ids)));
        }
        Transaction::query()
                   ->when($ids, fn($query) => $query->whereIn('id', $ids))
                   ->when(!$ids, fn($query) => $query->whereNull('sfid'))
                   ->orderBy('id', 'desc')
                   ->get()
                   ->each(function (Transaction $transaction) use ($io, $repository) {
                       $io->section('Transaction ' . $transaction->id);
                       $result = $repository->send_to_salesforce($transaction);
                       if ($result['success']) {
                           $io->success(sprintf('Transaction pushed to salesforce as %s', $result['sfid']));
                       } else {
                           $io->error('Failed to push transaction to salesforce');
                       }
                       $io->writeln($result['message']['text']);
                   });


        return Command::SUCCESS;
    }
}

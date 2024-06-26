<?php

namespace GPX\CLI\Coupon;

use GPX\CLI\BaseCommand;
use GPX\Model\OwnerCreditCoupon;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeactivateExpiredCouponsCommand extends BaseCommand {
    protected function configure(): void {
        $this->setName('coupon:expired');
        $this->setDescription('Deactivates expired coupons');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $io = $this->io($input, $output);
        $io->title('Deactivate expired coupons');

        OwnerCreditCoupon::active(true)
                         ->whereDate('expirationDate', '<', date('Y-m-d'))
                         ->update(['active' => false]);

        return Command::SUCCESS;
    }
}

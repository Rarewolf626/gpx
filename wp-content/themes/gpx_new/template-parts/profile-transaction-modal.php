<?php
/**
 * @var \GPX\Model\Transaction $transaction
 * @var \GPX\Model\UserMeta $agent
 */

use Illuminate\Support\Arr;
?>

<div class="profile-transaction-modal">
    <div class="profile-transaction-modal__header">
        <div class="profile-transaction-modal__title">
            <h3>Transaction <?= esc_html($transaction->id) ?></h3>
            <div>
                <?= $transaction->datetime->format('m/d/Y h:i a') ?>
            </div>
        </div>
        <div class="profile-transaction-modal__status">
            <?php if ($transaction->cancelled): ?>
                <h4>Cancelled</h4>
                <div><?= $transaction->cancelledDate?->format('m/d/Y') ?>
                    by <?= Arr::last($transaction->cancelledData)['name'] ?? '' ?></div>
            <?php else: ?>
                <form
                    method="post"
                    action="<?= esc_url(admin_url('admin-ajax.php')) ?>?action=gpx_agent_cancel_booking"
                    x-data="{
                        busy: false,
                        cancelled: false,
                        date: null,
                        agent: '<?= esc_attr($agent->getName()) ?>',
                        cancel() {
                            if (!confirm(`Are you sure you want to cancel this booking request?  The record will report that ${this.agent} cancelled the request.`)) {
                                return;
                            }
                            this.busy = true;
                            axios.post(this.$el.action, new FormData(this.$el))
                                .then(response => {
                                    if (!response.data.success) {
                                        this.busy = false;
                                        window.alertModal.alert(response.data.message || 'Failed to cancel booking request.');
                                        return;
                                    }
                                    this.cancelled = true;
                                    this.date = response.data.date;
                                    this.agent = response.data.agent;
                                    this.busy = false;
                                    if (response.data.message) {
                                        window.alertModal.alert(response.data.message, false, () => window.location.reload());
                                    } else {
                                        window.location.reload();
                                    }
                                })
                                .catch(error => {
                                    this.busy = false;
                                    window.alertModal.alert(error?.response.data?.message || 'Failed to cancel booking request.');
                                });
                        }
                    }"
                    @submit.prevent="cancel"
                >
                    <input type="hidden" name="transaction" value="<?= esc_attr($transaction->id) ?>" />
                    <input type="hidden" name="requester" value="user" />
                    <button
                        type="submit"
                        x-show="!cancelled && !busy"
                        class="btn btn-danger"
                        id="cancel-booking"
                        data-transaction="<?= esc_attr($transaction->id) ?>"
                        data-type="Cancel Booking"
                        data-agent="<?= esc_attr($agent->getName()) ?>"
                    >
                        Cancel Booking Request
                    </button>
                    <div x-show="busy"><i style="font-size:30px;" class="fa fa-spinner fa-spin"></i></div>
                    <div x-show="cancelled">
                        <h4>Cancelled</h4>
                        <div><span x-text="date"></span> by <span x-text="agent"></span></div>
                    </div>
                </form>

            <?php endif; ?>
        </div>
    </div>
    <div class="profile-transaction-modal__info">
        <div>
            <h4>Guest Info</h4>
            <table>
                <tr>
                    <th>Member Number:</th>
                    <td><?= esc_html($transaction->userID) ?></td>
                </tr>
                <tr>
                    <th>Member Name:</th>
                    <td><?= esc_html($transaction->user?->getName() ?? $transaction->transactionData['MemberName'] ?? '') ?></td>
                </tr>
                <tr>
                    <th>Guest Name:</th>
                    <td><?= esc_html($transaction->transactionData['GuestName'] ?? '') ?></td>
                </tr>
                <tr>
                    <th>Adults:</th>
                    <td><?= esc_html($transaction->transactionData['Adults'] ?? 1) ?></td>
                </tr>
                <tr>
                    <th>Children:</th>
                    <td><?= esc_html($transaction->transactionData['Children'] ?? 0) ?></td>
                </tr>
                <?php if (!empty($transaction->transactionData['specialRequest'])): ?>
                    <tr>
                        <th>Special Request:</th>
                        <td><?= esc_html($transaction->transactionData['specialRequest'] ?? '') ?></td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>
        <div>
            <h4>Resort / Room Info</h4>
            <table>
                <tr>
                    <th>Ref No:</th>
                    <td><?= esc_html($transaction->weekId ?? '') ?></td>
                </tr>
                <tr>
                    <th>Resort Name:</th>
                    <td><?= esc_html($transaction->transactionData['resortName'] ?? '') ?></td>
                </tr>
                <tr>
                    <th>Size:</th>
                    <td><?= esc_html($transaction->transactionData['Size'] ?? '') ?></td>
                </tr>
                <tr>
                    <th>Check In:</th>
                    <td><?= esc_html($transaction->check_in_date?->format('m/d/Y')) ?></td>
                </tr>
                <tr>
                    <th>Nights:</th>
                    <td><?= esc_html($transaction->transactionData['noNights'] ?? 0) ?></td>
                </tr>
            </table>

            <?php if ($transaction->deposit): ?>
                <h4>Deposit</h4>
                <table>
                    <tr>
                        <th>Ref No:</th>
                        <td><?= esc_html($transaction->deposit?->id ?? '') ?></td>
                    </tr>
                    <tr>
                        <th>Resort Name:</th>
                        <td><?= esc_html($transaction->deposit?->resort_name ?? '') ?></td>
                    </tr>
                    <tr>
                        <th>Deposit Year:</th>
                        <td><?= esc_html($transaction->deposit?->deposit_year ?? '') ?></td>
                    </tr>
                    <tr>
                        <th>Unit:</th>
                        <td><?= esc_html($transaction->deposit?->interval?->unitweek ?? '') ?></td>
                    </tr>
                </table>
            <?php endif; ?>
        </div>
        <div>
            <h4>Fees</h4>
            <table>

                <tr>
                    <th>Exchange/Rental Fee:</th>
                    <td><?= gpx_currency($transaction->transactionData['actWeekPrice'] ?? 0) ?></td>
                </tr>

                <?php if (($transaction->transactionData['actcpoFee'] ?? 0) > 0): ?>
                    <tr>
                        <th>Flex Booking Fee:</th>
                        <td><?= gpx_currency($transaction->transactionData['actcpoFee'] ?? 0) ?></td>
                    </tr>
                <?php endif; ?>
                <?php if (($transaction->transactionData['actupgradeFee'] ?? 0) > 0): ?>
                    <tr>
                        <th>Upgrade Fee:</th>
                        <td><?= gpx_currency($transaction->transactionData['actupgradeFee'] ?? 0) ?></td>
                    </tr>
                <?php endif; ?>
                <?php if (($transaction->transactionData['actguestFee'] ?? 0) > 0): ?>
                    <tr>
                        <th>Guest Fee:</th>
                        <td><?= gpx_currency($transaction->transactionData['actguestFee'] ?? 0) ?></td>
                    </tr>
                <?php endif; ?>
                <?php if (($transaction->transactionData['lateDepositFee'] ?? 0) > 0): ?>
                    <tr>
                        <th>Late Deposit Fee:</th>
                        <td><?= gpx_currency($transaction->transactionData['lateDepositFee'] ?? 0) ?></td>
                    </tr>
                <?php endif; ?>
                <?php if (($transaction->transactionData['actextensionFee'] ?? 0) > 0): ?>
                    <tr>
                        <th>Credit Extension Fee:</th>
                        <td><?= gpx_currency($transaction->transactionData['actextensionFee'] ?? 0) ?></td>
                    </tr>
                <?php endif; ?>
                <?php if ((gpx_parse_number($transaction->transactionData['couponDiscount'] ?? 0) + gpx_parse_number($transaction->transactionData['ownerCreditCouponAmount'] ?? 0)) > 0): ?>
                    <tr>
                        <th>Coupon Amount:</th>
                        <td>
                            <?= gpx_currency(gpx_parse_number($transaction->transactionData['couponDiscount'] ?? 0) + gpx_parse_number($transaction->transactionData['ownerCreditCouponAmount'] ?? 0)) ?>
                            <?php if (($transaction->transactionData['ownerCreditCouponAmount'] ?? 0) > 0): ?>
                                (MC: <?= gpx_currency($transaction->transactionData['ownerCreditCouponAmount'] ?? 0) ?>)
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endif; ?>
                <?php if (($transaction->transactionData['acttax'] ?? 0) > 0): ?>
                    <tr>
                        <th>Tax Charged:</th>
                        <td><?= gpx_currency($transaction->transactionData['acttax'] ?? 0) ?></td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <th>Paid:</th>
                    <td><?= gpx_currency($transaction->transactionData['Paid'] ?? 0) ?></td>
                </tr>
                <?php if (($transaction->transactionData['refunded'] ?? 0) > 0): ?>
                    <tr>
                        <th>Refunded:</th>
                        <td><?= gpx_currency($transaction->transactionData['refunded'] ?? 0) ?></td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

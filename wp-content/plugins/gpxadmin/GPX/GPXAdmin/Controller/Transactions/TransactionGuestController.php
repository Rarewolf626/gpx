<?php

namespace GPX\GPXAdmin\Controller\Transactions;

use SObject;
use GPX\Model\UserMeta;
use GPX\Model\Transaction;
use GPX\Api\Salesforce\Salesforce;
use GPX\Command\Transaction\UpdateGuestInfo;
use GPX\Form\Admin\Transaction\TransactionGuestForm;

class TransactionGuestController {
    public function index() {
        $id = gpx_request('transaction');
        if (!$id) {
            wp_send_json(['success' => false, 'message' => 'No transaction ID provided.']);
        }
        $transaction = Transaction::find($id);
        if (!$transaction) {
            wp_send_json(['success' => false, 'message' => 'Transaction not found.']);
        }
        if (!$transaction->isBooking()) {
            wp_send_json(['success' => false, 'message' => 'This is not a booking transaction.']);
        }

        $data = $transaction->data;

        $name = explode(" ", $data['GuestName'] ?? ' ', 2);
        if (empty($data['GuestFirstName'])) {
            $data['GuestFirstName'] = $name[0] ?? null;
        }
        if (empty($data['GuestLastName'])) {
            $data['GuestLastName'] = $name[1] ?? null;
        }

        wp_send_json([
            'success' => true,
            'guest' => [
                'id' => $transaction->id,
                'cancelled' => $transaction->cancelled,
                'first_name' => $data['GuestFirstName'] ?? '',
                'last_name' => $data['GuestLastName'] ?? '',
                'email' => $data['GuestEmail'] ?? $data['Email'] ?? '',
                'phone' => gpx_format_phone($data['GuestPhone'] ?? '') ?? '',
                'adults' => (int) ($data['Adults'] ?? 1),
                'children' => (int) ($data['Children'] ?? 0),
                'owner' => $data['OwnerName'] ?? $data['Owner'] ?? '',
            ],
        ]);
    }

    public function save(TransactionGuestForm $form) {
        $values = $form->validate();
        $id = $values['id'];
        if (!$id) {
            wp_send_json(['success' => false, 'message' => 'No transaction ID provided.']);
        }
        $transaction = Transaction::find($id);
        if (!$transaction) {
            wp_send_json(['success' => false, 'message' => 'Transaction not found.']);
        }
        if ($transaction->cancelled) {
            wp_send_json(['success' => false, 'message' => 'Transaction is cancelled.']);
        }
        if (!$transaction->isBooking()) {
            wp_send_json(['success' => false, 'message' => 'This is not a booking transaction.']);
        }


        gpx_dispatch(new UpdateGuestInfo($transaction, $values));

        wp_send_json(['success' => true, 'message' => 'Guest Info updated.']);
    }
}

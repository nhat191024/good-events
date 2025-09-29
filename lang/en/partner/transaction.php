<?php
return [
    'wallet' => 'Wallet',
    'no_transaction' => 'No transactions',
    'no_transaction_description' => 'You have no transactions in your wallet yet.',

    'types' => [
        'deposit' => 'Deposit',
        'withdrawal' => 'Withdrawal',
    ],

    'label' => [
        'id' => 'ID',
        'type' => 'Transaction Type',
        'amount' => 'Amount',
        'reason' => 'Reason',
        'balance' => 'Current Balance',
        'created_at' => 'Created At',
    ],

    'button' => [
        'add_funds' => 'Add Funds',
        'submit' => 'Submit',
    ],

    'modal' => [
        'add_funds_title' => 'Add Funds to Wallet',
        'add_funds_description' => 'Enter the amount you want to add to your wallet.',
    ],

    'form' => [
        'amount' => 'Amount',
        'amount_placeholder' => 'Enter amount to add (VND)',
    ],

    'notification' => [
        'add_funds_initiated' => 'Add Funds Request',
        'add_funds_amount' => 'Request to add :amount has been initiated.',

        'add_funds_success' => 'Payment Successful',
        'add_funds_success_message' => 'Your payment was successful. Transaction ID: :transactionId',
    ],
];

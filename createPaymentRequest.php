<?php
/**
 * Paypa Plane sample php script to create a payment request 
 *
 * Log into https://staging-admin.paypaplane.com to acquire the following details
 */

// Create a token at staging-admin.paypaplane.com under Account -> Api Keys [mandatory]
define('TOKEN', '');
// The site guid can be found under Account  -> Sites, click "manage" on the site, scroll down till you see GUID [mandatory]
define('SITE_ID', '');
// The account id can be found under Account -> Details, "Account ID" field [mandatory]
define('ACCOUNT_ID', '');

function createPaymentRequest($data) 
{
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, 'https://staging-api.paypaplane.com/v1.0/payment-requests');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
	curl_setopt($ch, CURLOPT_POST, 1);

	$headers = array();
	$headers[] = 'Authorization: Bearer ' . TOKEN;
	$headers[] = 'Cache-Control: no-cache';
	$headers[] = 'Content-Type: application/json';
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	$result = curl_exec($ch);
	if (curl_errno($ch)) {
		echo 'Error:' . curl_error($ch);
	}
	curl_close ($ch);

	return json_decode($result, true);
}

// See documentation for details about payment request fields
// Documentation is available at https://dev-developers.paypaplane.com/

$baseMetadata = [
    'type' => 'description',
    'data' => [
        'description' => 'Membership Fees'
    ]
];

$stages = [[
	'frequency' => 2,
	'period' => 'Week',
	'duration' => 0,
	'description' => 'Fortnightly Payment',
	'amount' => 50,
	'withdrawDay' => 'sale'
]];

$req = [
    'accountId' => ACCOUNT_ID,
    'siteId' => SITE_ID,
    'amount' => [
        'amount' => 0,
        'currency' => 'AUD'
    ],
    // 'isRecurring' => true,
    'recurrence' => [
        'startDate' => '2019-06-16T10:00:00Z',
        'stages' => [],
        'backdate' => true,
        'minimumDaysUntilFirstPayment' => 0
    ],
    'transactionTypes' =>  ['visa', 'mastercard', 'amex', 'btau_credit'],
    'lead' => [
		'email' => '',
		'mobile' => '0400111222', 
		'name' => 'New Members Name',
		'sendSms' => false
	]
];

$req['recurrence']['stages'] = $stages;
$req['metadata'] = $baseMetadata;
$req['reference'] = '';

$res = createPaymentRequest($req);

if (is_array($res) && isset($res['id'])) {
	echo "\nSuccess " . $req['lead']['name'] . ' => ' . $res['id']."\n";
} else {
	echo "\nERROR " . $req['lead']['name'] . ' => ' . print_r($res)."\n";
}

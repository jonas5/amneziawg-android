<?php

require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';

$gatewayModuleName = basename(__FILE__, '.php');

$gatewayParams = getGatewayVariables($gatewayModuleName);

if (!$gatewayParams['type']) {
    die("Module Not Activated");
}

$payload = @file_get_contents('php://input');
$data = json_decode($payload, true);

$invoiceId = $data['invoice_id'];
$transactionId = $data['transaction_id'];
$paymentAmount = $data['amount'];
$paymentFee = 0;
$transactionStatus = $data['status'];

$invoiceId = checkCbInvoiceID($invoiceId, $gatewayParams['name']);

checkCbTransID($transactionId);

if ($transactionStatus == 'paid') {
    addInvoicePayment(
        $invoiceId,
        $transactionId,
        $paymentAmount,
        $paymentFee,
        $gatewayModuleName
    );
    logTransaction($gatewayParams['name'], $data, 'Successful');
} else {
    logTransaction($gatewayParams['name'], $data, 'Unsuccessful');
}

header("HTTP/1.1 200 OK");

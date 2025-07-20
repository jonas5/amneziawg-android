<?php

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

function creem_MetaData()
{
    return array(
        'DisplayName' => 'Creem',
        'APIVersion' => '1.1', // Use 1.1 for all modules that support CVV.
        'DisableLocalCreditCardInput' => true,
        'TokenisedStorage' => false,
    );
}

function creem_config()
{
    return array(
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'Creem',
        ),
        'apiKey' => array(
            'FriendlyName' => 'API Key',
            'Type' => 'text',
            'Size' => '50',
            'Default' => '',
            'Description' => 'Enter your Creem API key here.',
        ),
        'testMode' => array(
            'FriendlyName' => 'Test Mode',
            'Type' => 'yesno',
            'Description' => 'Tick to enable test mode.',
        ),
    );
}

function creem_link($params)
{
    // Gateway Configuration Parameters
    $apiKey = $params['apiKey'];
    $testMode = $params['testMode'];

    // Invoice Parameters
    $invoiceId = $params['invoiceid'];
    $description = $params["description"];
    $amount = $params['amount'];
    $currencyCode = $params['currency'];

    // Client Parameters
    $firstname = $params['clientdetails']['firstname'];
    $lastname = $params['clientdetails']['lastname'];
    $email = $params['clientdetails']['email'];
    $address1 = $params['clientdetails']['address1'];
    $address2 = $params['clientdetails']['address2'];
    $city = $params['clientdetails']['city'];
    $state = $params['clientdetails']['state'];
    $postcode = $params['clientdetails']['postcode'];
    $country = $params['clientdetails']['country'];
    $phone = $params['clientdetails']['phonenumber'];

    // System Parameters
    $systemUrl = $params['systemurl'];
    $returnUrl = $params['returnurl'];
    $langPayNow = $params['langpaynow'];

    $url = 'https://api.creem.io/v1/checkout';

    $postfields = array(
        'amount' => $amount,
        'currency' => $currencyCode,
        'description' => $description,
        'invoice_id' => $invoiceId,
        'customer' => array(
            'email' => $email,
            'name' => $firstname . ' ' . $lastname,
        ),
        'success_url' => $returnUrl,
        'cancel_url' => $returnUrl,
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postfields));

    $headers = array();
    $headers[] = 'Content-Type: application/json';
    $headers[] = 'x-api-key: ' . $apiKey;
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        return 'Error:' . curl_error($ch);
    }
    curl_close($ch);

    $result = json_decode($result, true);

    if (isset($result['url'])) {
        $htmlOutput = '<form method="get" action="' . $result['url'] . '">';
        $htmlOutput .= '<input type="submit" value="' . $langPayNow . '" />';
        $htmlOutput .= '</form>';
    } else {
        $htmlOutput = 'Error creating checkout session.';
    }


    return $htmlOutput;
}

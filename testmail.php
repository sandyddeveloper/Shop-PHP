<?php

require './vendor/autoload.php';

$email = "ro932245@gmail.com";
$app_password = "qatsftlrkegwnsht";


use PhpImap\Exceptions\ConnectionException;
use PhpImap\Mailbox;

function imap_scanner($amount, $currency, $cashapp_note, $email, $app_password) {
    try {
        $mailbox = new PhpImap\Mailbox(
            '{imap.gmail.com:993/imap/ssl/novalidate-cert}INBOX', // IMAP server and mailbox folder
            $email, // Username for the before configured mailbox
            $app_password, // Password for the before configured username
            __DIR__, // Directory, where attachments will be saved (optional)
            'US-ASCII' // Server encoding (optional)
        );

        $mailbox->setAttachmentsIgnore(true);

        $search = 'SUBJECT "sent you ' .'$' . $amount . ' for ' . $cashapp_note . '"';

        $mail_ids = $mailbox->searchMailbox($search);
    } catch (ConnectionException $ex) {
        exit('IMAP connection failed: '.$ex->getMessage());
    } catch (Exception $ex) {
        exit('An error occurred: '.$ex->getMessage());
    }

    foreach ($mail_ids as $mail_id) {
        $email = $mailbox->getMail(
            $mail_id, // ID of the email, you want to get
            false // Do NOT mark emails as seen (optional)
        );

        // check if email exists
        if ($email) {
            // sender email
            $sender = $email->fromAddress;

            //if ($sender != 'cash@square.com') {
            if ($sender != 'riskysells@gmail.com') {
                $return = array(
                    'code' => false
                );
                return $return;
            }

            // get email subject
            $subject = $email->subject;

            // explode by space
            $subject = explode(" ", $subject);
            $user = "";
            foreach ($subject as $value) {
                if ($value == 'sent') {
                    break;
                } else {
                    $user .= $value.' ';
                }
            }

            $return = array(
                'user' => $user,
                'code' => true
            );

            return $return;
        }
    }

    $mailbox->disconnect();

    $return = array(
        'code' => false
    );

    return $return;
}

$email = "p58321643@gmail.com";
$app_password = "vyjjsbgpcgbeqjrv";
$currency = 'USD';
$amount = '1';
$cashapp_note = '3-FoJ61';

$result = imap_scanner($amount, $currency, $cashapp_note, $email, $app_password);

if ($result['code']) {
    echo "Email found!<br>";
    echo "User: " . $result['user'];
} else {
    echo "No matching email found.<br>";
}



// $url = "{imap.gmail.com:993/imap/ssl/novalidate-cert}INBOX";
// $id = "ro932245@gmail.com";
// $pwd = "qatsftlrkegwnsht";
// $currency = 'USD'; // Replace with the actual currency
// $amount = '4';    // Replace with the actual amount
// $cashapp_note = 'classy4e'; // Replace with the actual cashapp_note

// $search = 'SUBJECT "sent you ' .'$' . $amount . ' for ' . $cashapp_note . '"';

// $imap = imap_open($url, $id, $pwd);

// print("Connection established...." . "<br>");

// // Searching emails
// $emailData = imap_search($imap, $search);

// if (!empty($emailData)) {
//     print("Email found!<br>");
// } else {
//     print("No matching email found.<br>");
// }

// // Closing the connection
// imap_close($imap);


// $mailbox = imap_open('{imap.gmail.com:993/imap/ssl/novalidate-cert}INBOX', '$emails', '$app_password');
// if ($mailbox) {
//     echo 'Connected successfully!';
//     imap_close($mailbox);
// } else {
//     echo 'Connection failed: ' . imap_last_error();
// }


?>
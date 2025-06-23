<?php

// -------------------------------------------- Start of snippet -------------------------------------------- //
// Path: include\oglobal.php
// Version: 1.0.0
require './vendor/autoload.php';


use PhpImap\Exceptions\ConnectionException;
use PhpImap\Mailbox;



function imap_scaner($amount, $currency, $cashapp_note)
{
    $email = "p58321643@gmail.com";
    $app_password = "vyjjsbgpcgbeqjrv";

    try {
        $mailbox = new PhpImap\Mailbox(
            '{imap.gmail.com:993/imap/ssl/novalidate-cert}INBOX', // IMAP server and mailbox folder
            $email, // Username for the before configured mailbox
            $app_password, // Password for the before configured username
            __DIR__, // Directory, where attachments will be saved (optional)
            'US-ASCII' // Server encoding (optional)
        );

        $mailbox->setAttachmentsIgnore(true);

        // Use the exact search string as in your original code
        $search = 'SUBJECT "sent you $' . $amount . ' for ' . $cashapp_note . '"';

        $mail_ids = $mailbox->searchMailbox($search);
    } catch (ConnectionException $ex) {
        exit('IMAP connection failed: ' . $ex->getMessage());
    } catch (Exception $ex) {
        exit('An error occurred: ' . $ex->getMessage());
    }

    // Output the IDs of retrieved emails for debugging

    foreach ($mail_ids as $mail_id) {
        $emailObj = $mailbox->getMail(
            $mail_id, // ID of the email, you want to get
            false // Do NOT mark emails as seen (optional)
        );


        // Extract user and other details from the subject
        $subjectParts = explode(" ", $emailObj->subject);
        $user = "";
        foreach ($subjectParts as $value) {
            if ($value == 'sent') {
                break;
            } else {
                $user .= $value . ' ';
            }
        }

        $return = array(
            'user' => $user,
            'code' => true
        );

        return $return;
    }

    $mailbox->disconnect();

    $return = array(
        'code' => false
    );

    return $return;
}


function imap_scaner_new($amount, $currency, $cashapp_note, $user)
{
    $email = "p58321643@gmail.com";
    $app_password = "vyjjsbgpcgbeqjrv";

    try {
        $mailbox = new PhpImap\Mailbox(
            '{imap.gmail.com:993/imap/ssl/novalidate-cert}INBOX', // IMAP server and mailbox folder
            $email, // Username for the before configured mailbox
            $app_password, // Password for the before configured username
            __DIR__, // Directory, where attachments will be saved (optional)
            'US-ASCII' // Server encoding (optional)
        );

        $mailbox->setAttachmentsIgnore(true);

        // Use the exact search string as in your original code
    $search = 'SUBJECT "' . $user . ' sent you $' . $amount . ' for ' . $cashapp_note . '"';

        $mail_ids = $mailbox->searchMailbox($search);
    } catch (ConnectionException $ex) {
        exit('IMAP connection failed: ' . $ex->getMessage());
    } catch (Exception $ex) {
        exit('An error occurred: ' . $ex->getMessage());
    }

   foreach ($mail_ids as $mail_id) {
        $emailObj = $mailbox->getMail(
            $mail_id, // ID of the email, you want to get
            false // Do NOT mark emails as seen (optional)
        );

        // Check if email exists
        if ($emailObj) {
            // Sender email
            $sender = $emailObj->fromAddress;

            return true;
        }
    }

    $mailbox->disconnect();

    return false;
}




function imap_scaner_conf($amount, $currency, $cashapp_note, $user)
{
   $email = "p58321643@gmail.com";
    $app_password = "vyjjsbgpcgbeqjrv";


    $mailbox = new PhpImap\Mailbox(
        '{imap.gmail.com:993/imap/ssl/novalidate-cert}INBOX', // IMAP server and mailbox folder
        $email, // Username for the before configured mailbox
        $app_password, // Password for the before configured username
        __DIR__, // Directory, where attachments will be saved (optional)
        'US-ASCII' // Server encoding (optional)
    );

    $mailbox->setAttachmentsIgnore(true);

    $search = 'SUBJECT "You accepted $' . $amount . ' from ' . $user . ' for ' . $cashapp_note . '"';

    try {
        $mail_ids = $mailbox->searchMailbox($search);
    } catch (ConnectionException $ex) {
        exit('IMAP connection failed: ' . $ex->getMessage());
    } catch (Exception $ex) {
        exit('An error occurred: ' . $ex->getMessage());
    }

    foreach ($mail_ids as $mail_id) {
        $emailObj = $mailbox->getMail(
            $mail_id, // ID of the email, you want to get
            false // Do NOT mark emails as seen (optional)
        );

        // Check if email exists
        if ($emailObj) {
            // Sender email
            $sender = $emailObj->fromAddress;

            return true;
        }
    }

    $mailbox->disconnect();

    return false;
}

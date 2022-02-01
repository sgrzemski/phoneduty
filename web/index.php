<?php

/**
 *
 * Twilio twimlet for forwarding inbound calls
 * to the on-call engineer as defined in PagerDuty
 *
 * Designed to be hosted on Heroku
 *
 * (c) 2014 Vend Ltd.
 *
 */

require __DIR__ . '/../vendor/autoload.php';

// Set these Heroku config variables
$scheduleID = getenv('PAGERDUTY_SCHEDULE_ID');
$APItoken   = getenv('PAGERDUTY_API_TOKEN');

// Should we announce the local time of the on-call person?
// (helps raise awareness you might be getting somebody out of bed)
$announceTime = getenv('PHONEDUTY_ANNOUNCE_TIME');


$pagerduty = new \Vend\Phoneduty\Pagerduty($APItoken);

$userID = $pagerduty->getOncallUserForSchedule($scheduleID);

if (null !== $userID) {
    $user = $pagerduty->getUserDetails($userID);

    $attributes = [
        'voice' => 'man',
        'language' => 'pl-pl'
    ];

    $time = "";
    if ($announceTime && $user['local_time']) {
        $time = sprintf("Obecna godzina w ich strefie czasowej to %s.", $user['local_time']->format('g:ia'));
    }


    if (isset($_GET['call'])) {

        $response = sprintf("Osobą odbywającą obecnie dyżur technologiczny jest %s %s. %s "
        . "Proszę czekać, za chwilę nastąpi przekierowanie.",
        $user['first_name'],
        $user['last_name'],
        $time
        );

        $twilioResponse = new \Twilio\TwiML\VoiceResponse();
        $twilioResponse->say($response, $attributes);
        $twilioResponse->dial($user['phone_number']);
    }

    if (isset($_GET['sms']) {

        $twilioResponse = new \Twilio\TwiML\MessagingResponse();;
        $twilioResponse->message('Test SMS');

    }

    // send response
    if (!headers_sent()) {
        header('Content-type: text/xml');
    }

    echo $twilioResponse;
}
?>

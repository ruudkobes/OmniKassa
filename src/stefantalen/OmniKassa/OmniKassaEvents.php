<?php

namespace stefantalen\OmniKassa;

final class OmniKassaEvents
{
    const SUCCESS = 0;
    const FAILURE = 5;
    const CANCELLED = 17;
    const OPEN = 60;
    const SERVER_UNREACHABLE = 90;
    const EXPIRED = 97;

    public static function getMessage($code)
    {
        $code = intval($code);
        return isset(self::$messages[$code]) ? self::$messages[$code] : sprintf('Foutcode "%s" is niet bekend. Neem contact op met de beheerder.', $code);
    }

    protected static $messages = array(
        0 => 'Betaling geslaagd',
        2 => 'Please call the bank because the authorization limit on the card has been exceeded.',
        3 => 'Er ging iets mis, neem alstublieft contact op met Arjan of Ruud',
        5 => 'Do not honor, authorization refused',
        12 => 'Invalid transaction, check the parameters sent in the request',
        14 => 'Invalid card number or invalid Card Security Code or Card (for MasterCard) or invalid Card Verification Value (for Visa/Maestro)',
        17 => 'U hebt de betaling afgebroken.',
        24 => 'Invalid status',
        25 => 'Transaction not found in database',
        30 => 'Invalid format',
        34 => 'Fraud suspicion',
        40 => 'Operation not allowed to this Merchant',
        60 => 'Pending transaction',
        63 => 'Security breach detected, transaction stopped',
        75 => 'The number of attempts to enter the card number has been exceeded (three tries exhausted)',
        90 => 'Server niet bereikbaar. De transactie is afgebroken, probeer het later nog eens',
        94 => 'Duplicate transaction',
        97 => 'Request time-out; transaction refused',
        99 => 'Payment page temporarily unavailable'
    );
}
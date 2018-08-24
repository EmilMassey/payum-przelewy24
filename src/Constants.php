<?php

namespace EmilMassey\Payum\Przelewy24;

final class Constants
{
    const DEFAULT_ENDPOINT = 'https://secure.przelewy24.pl/';
    const SANDBOX_ENDPOINT = 'https://sandbox.przelewy24.pl/';

    const API_VERSION = 3.2;

    const ACTION_REGISTER = 'trnRegister';
    const ACTION_REDIRECT = 'trnRequest';
    const ACTION_VERIFY = 'trnVerify';

    const ALLOWED_CURRENCIES = ['PLN', 'EUR', 'GBP', 'CZK'];

    const STATUS_NEW = 'new';
    const STATUS_PENDING = 'pending';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELED = 'canceled';
    const STATUS_EXPIRED = 'expired';

    const CHANNEL_CARD = 1;
    const CHANNEL_TRANSFER = 2;
    const CHANNEL_TRANSFER_TRADITIONAL = 4;
    const CHANNEL_24_7 = 16;
    const CHANNEL_PREPAID = 32;
}

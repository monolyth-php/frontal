<?php

namespace monolyth;

interface User_Constants
{
    const NAME_IS_EMAIL = false;

    const STATUS_ACTIVATE = 1;
    const STATUS_REACTIVATE = 2;
    const STATUS_INACTIVE = 3;
    const STATUS_DISABLED = 4;
    const STATUS_INVALID_EMAIL = 8;
    const STATUS_EMAIL_UNCONFIRMED = 16;
    const STATUS_UNCONFIRMED = 32;
    // Users are considered male by default. Sue. me.
    const STATUS_FEMALE = 64;
    const STATUS_GENERATED_PASS = 128;

    const FEATURE_OPTIN = 1;
    const FEATURE_NEWS = 2;
}


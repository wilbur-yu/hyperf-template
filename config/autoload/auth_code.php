<?php

declare(strict_types=1);

/**
 * This file is part of project burton.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 */

return [
    'secret_key' => env('AUTH_CODE_SECRET_KEY', env('AES_KEY', 'burton.academy')),
    'dynamic_key_length' => (int)env('AUTH_CODE_DYNAMIC_KEY_LENGTH', 4),
];

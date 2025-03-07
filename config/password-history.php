<?php

// config for Beliven/PasswordHistory
return [
    // Use -1 for unlimited history
    // @phpstan-ignore-next-line
    'depth' => (int) env('PASSWORD_HISTORY_DEPTH', 10),
];

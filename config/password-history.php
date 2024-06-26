<?php

// config for Beliven/PasswordHistory
return [
    // Use -1 for unlimited history
    'depth' => (int) env('PASSWORD_HISTORY_DEPTH', 10),
];

<?php

return [
    'mailer' => getenv('MAIL_MAILER') ?: 'smtp',
    'host' => getenv('MAIL_HOST') ?: 'academia.gocreative.cl',
    'port' => (int) (getenv('MAIL_PORT') ?: 465),
    'username' => getenv('MAIL_USERNAME') ?: 'notificacion@academia.gocreative.cl',
    'password' => getenv('MAIL_PASSWORD') ?: 'G;bD1;5z_$b1{NF2',
    'encryption' => getenv('MAIL_ENCRYPTION') ?: 'ssl',
    'from_address' => getenv('MAIL_FROM_ADDRESS') ?: 'notificacion@academia.gocreative.cl',
    'from_name' => getenv('MAIL_FROM_NAME') ?: 'Academia Iquique',
];

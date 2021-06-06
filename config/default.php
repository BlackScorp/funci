<?php

define('CONFIG_DIR',ROOT_DIR.'/config');
define('TEMPLATE_DIRS',[
    ROOT_DIR.'/templates/'
    
]);

define('LOG_DIR', ROOT_DIR.'/logs');

define('OPENSSL_CONFIG',[
        'config' => '/etc/ssl/openssl.cnf',
        'private_key_bits'=> 2048,
        'default_md' => 'sha256',
]);
//var_dump(openssl_get_cipher_methods());
define('OPENSS_CIPHER','aes256');
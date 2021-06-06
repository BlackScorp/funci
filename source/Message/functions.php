<?php

function createInitialKeys(string $username,string $password):void{
    $keyFile = KEY_DIR.'/'.$username;

    $key =openssl_pkey_new(OPENSSL_CONFIG);

    openssl_pkey_export($key, $privateKeyString, $password );
    $publicKeySting = openssl_pkey_get_details($key)['key'];


    file_put_contents($keyFile.'.pub',$publicKeySting);
    file_put_contents($keyFile,$privateKeyString);
}

function sendSealedMessageTo(array $recipients,$subject,$message):bool{
    if(0===count($recipients)){
        return true;
    }
    $publicKeys = [];
    $encryptedKeys=[];

    foreach($recipients as $recipient){
        $keyFileName = KEY_DIR.'/'.$recipient.'.pub';
        if(!is_file($keyFileName)){
            warning('Public Key for recipient not found',['recipient'=>$recipient]);
            continue;
        }
        $publicKeyString = file_get_contents($keyFileName);
        $publicKeys[]=openssl_get_publickey($publicKeyString);
    }
    $iv = openssl_random_pseudo_bytes(32);
    openssl_seal($message,$sealedMessage,$encryptedKeys,$publicKeys,OPENSSL_CIPHER,$iv);

    $messageFileName = convertSubjectToFileName($subject);

    foreach($encryptedKeys as $recipientNumber => $encryptedKey){
        $content = base64_encode($sealedMessage).KEY_MESSAGE_SEPARATOR.base64_encode($encryptedKey).KEY_MESSAGE_SEPARATOR.base64_encode($iv);
        $recipient = $recipients[$recipientNumber];
        if(!is_dir(SEALED_MESSAGE_DIR.'/'.$recipient)){
            mkdir(SEALED_MESSAGE_DIR.'/'.$recipient);
        }
        file_put_contents(SEALED_MESSAGE_DIR.'/'.$recipient.'/'.$messageFileName,$content);
    }


    return true;
}

function convertSubjectToFileName(string $subject):string{
    $subject = trim($subject);
    $messageFileName = preg_replace('/[^A-z0-9-]/m','_',$subject);
    $messageFileName = preg_replace('/_{2,}/m','_',$messageFileName);
    return $messageFileName.'.message';
}

function getSealedMessageContent(string $username,string $password,string $subject):string{
    $privateKey =  KEY_DIR.'/'.$username;
    if(!is_file($privateKey)){
        return 'Private Key not found for user';
    }
    $privateKey = openssl_get_privatekey(file_get_contents($privateKey),$password);
    if(!$privateKey){
        return 'Invalid password';
    }
    $messageFile = SEALED_MESSAGE_DIR.'/'.$username.'/'.convertSubjectToFileName($subject);
    if(!is_file($messageFile)){
        return 'File not exists';
    }
    $content = file_get_contents($messageFile);
    [$sealedMessage,$messageKey,$iv] = explode(KEY_MESSAGE_SEPARATOR,$content);
    $sealedMessage = base64_decode($sealedMessage);
    $messageKey = base64_decode($messageKey);
    $iv = base64_decode($iv);

    $messageText ='';

    if (openssl_open($sealedMessage, $messageText, ($messageKey), $privateKey,OPENSSL_CIPHER,$iv ) ) {
        return $messageText;
    }
    return 'Failed to read message';
}


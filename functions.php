<?php

function logger($value)
{
    $msg = date('Y-m-d H:i:s') . ': ' . $value . PHP_EOL;

    $fh = fopen(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'log.txt', 'a');
    fwrite($fh, $msg);
    fclose($fh);

    echo $msg;
}

// taken from https://www.php.net/manual/en/function.array-diff.php#91756
function array_diff_recursive($aArray1, $aArray2)
{
    $aReturn = array();

    foreach ($aArray1 as $mKey => $mValue) {
        if (array_key_exists($mKey, $aArray2)) {
            if (is_array($mValue)) {
                $aRecursiveDiff = array_diff_recursive($mValue, $aArray2[$mKey]);
                if (count($aRecursiveDiff)) { $aReturn[$mKey] = $aRecursiveDiff; }
            } else {
                if ($mValue != $aArray2[$mKey]) {
                    $aReturn[$mKey] = $mValue;
                }
            }
        } else {
            $aReturn[$mKey] = $mValue;
        }
    }

    return $aReturn;
}

function sendMail(array $differences): bool
{
    $config = require('config.php');
    $config = $config['mail'];

    $transport = (new Swift_SmtpTransport($config['host'], $config['port']))
        ->setUsername($config['user'])
        ->setPassword($config['pass']);

    $mailer = new Swift_Mailer($transport);

    $message = (new Swift_Message('SGD changes found!'))
        ->setFrom([$config['user'] => 'SGD Notify'])
        ->setTo($config['recipient'])
        ->setBody('Some differences were found: ' . var_export($differences, true));

    return $mailer->send($message);
}
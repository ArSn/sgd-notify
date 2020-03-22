<?php

// disabling just in time as it is disabled on DH
ini_set('pcre.jit', '0');

require 'vendor/autoload.php';
require 'LatestData.php';
require 'functions.php';

logger('------------- New execution starting now! -------------');

if (!file_exists( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config.php')) {
    logger('No config file found! Create a config.php based on config.example.php');
    die;
}
$config = require(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config.php');

$jar = new \GuzzleHttp\Cookie\CookieJar;
$client = new GuzzleHttp\Client([
    'cookies' => $jar,
]);
$response = $client->request('GET', 'https://www.sgd-campus.de/api/v1/login/login', [
    'headers' => [
        'Host' => 'www.sgd-campus.de',
        'username' => $config['user'],
        'password' => $config['pass'],
    ],
]);

if ($response->getStatusCode() == 200) {
    logger('Login successful!');
} else {
    logger('Login not successful!');
    die;
}

$response = $client->request('GET', 'https://www.sgd-campus.de/servlet/CurriculumOverview?display=main&cmd=3&userid=null&ilgid=332513');

if ($response->getStatusCode() == 200) {
    logger('Getting course overview successful!');
} else {
    logger('Getting course overview not successful!');
    die;
}

$body = (string)$response->getBody();

preg_match("/\'iCurriculumJSON\': (.*)/m", $body, $matches);

$latest = new LatestData();
$currentData = json_decode(trim(trim($matches[1]), ','), true);

$difference = array_diff_recursive($currentData, $latest->getLatestData());
if (!empty($difference)) {
    logger('There is a difference: ' . var_export($difference, true));

    if (sendMail($difference)) {
        logger('Sent notification mail successfuly.');
    } else {
        logger('Could not send notification mail.');
    }
    $latest->setLatestData($currentData);
} else {
    logger('No difference found, not taking any action.');
}

logger('Done!');
<?php

require 'vendor/autoload.php';
if (!file_exists('config.php')) {
    logger('No config file found! Create a config.php based on config.example.php');
    die;
}
$config = require('config.php');

function logger($value) {
    echo $value . PHP_EOL;
}

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

$data = json_decode(trim(trim($matches[1]), ','), true);

var_dump($data);


logger('Done!');
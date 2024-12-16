<?php

use Symfony\Component\Dotenv\Dotenv;

require __DIR__ . '/vendor/autoload.php';

// php app.php advanced-centrifuge
// php app.php https://mods.factorio.com/mod/Bluetonium

$mod_name = explode('https://mods.factorio.com/mod/', $argv[1]);
$mod_name = end($mod_name);

dump($mod_name);

$guzzle = new GuzzleHttp\Client();

$body = $guzzle->get("https://mods.factorio.com/api/mods/{$mod_name}/full")->getBody()->getContents();
$json = json_decode($body, true);

//dump($json);
$releases = $json['releases'];

$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env');

foreach ($releases as $release) {
    dump($release['file_name']);
    $pathname = __DIR__ . '/downloads/' . $release['file_name'];

    if (!file_exists($pathname) || sha1(file_get_contents($pathname)) != $release['sha1']) {
        $file = $guzzle->get("https://mods.factorio.com" . $release['download_url'], [
            'query' => [
                'username' => $_ENV['FACTORIO_USERNAME'],
                'token' => $_ENV['FACTORIO_TOKEN'],
            ],
        ])->getBody()->getContents();

        if (sha1($file) != $release['sha1']) throw new Exception();

        file_put_contents($pathname, $file);
    }

    // todo: unzip
}

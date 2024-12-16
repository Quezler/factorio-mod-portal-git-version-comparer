<?php

use Symfony\Component\Dotenv\Dotenv;

require __DIR__ . '/vendor/autoload.php';

// php app.php advanced-centrifuge
// php app.php https://mods.factorio.com/mod/Bluetonium

$mod_name = explode('https://mods.factorio.com/mod/', $argv[1]);
$mod_name = end($mod_name);

dump($mod_name);

$guzzle = new GuzzleHttp\Client([
    'headers' => [
        'user-agent' => 'https://github.com/Quezler/factorio-mod-portal-git-version-comparer',
    ],
]);

$body = $guzzle->get("https://mods.factorio.com/api/mods/{$mod_name}/full")->getBody()->getContents();
$json = json_decode($body, true);

//dump($json);
$releases = $json['releases'];

$repository_directory = __DIR__ . '/repositories/' . $mod_name;
if (!file_exists($repository_directory)) {
    mkdir($repository_directory);
    passthru("cd {$repository_directory} && git init");
}

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

    $pathname_unzipped = str_replace('.zip', '', $pathname);
    if (!file_exists($pathname_unzipped)) {
        $zip = new ZipArchive;
        $zip->open($pathname);
//        $zip->extractTo(__DIR__ . '/downloads/' . $mod_name);
        $zip->extractTo($pathname_unzipped);
        $zip->close();
    }
}

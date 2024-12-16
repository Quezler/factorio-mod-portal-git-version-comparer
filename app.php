<?php

require __DIR__ . '/vendor/autoload.php';

// php app.php https://mods.factorio.com/mod/advanced-centrifuge
// php app.php advanced-centrifuge
$mod_name = explode('https://mods.factorio.com/mod/', $argv[1]);
$mod_name = end($mod_name);

dd($mod_name);

<?php

use Nahkampf\PhpKeypressWindows\Keypress;

require "vendor/autoload.php";

const TIMEOUT = 30;
$keypress = new Keypress();
$i = $keypress->key;
echo "You are using input method '" . $keypress->methods[$keypress->method] . "' with OS set to " . $keypress->os;
input:
echo "\nPlease press X: ";
$key = $i->read(TIMEOUT);
print_r($key);
goto input;
exit;

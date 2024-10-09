<?php

require "vendor/autoload.php";
use Nahkampf\PhpKeypressWindows\Keypress;

// Initialize the keypress reader
try {
    $keypress = new Keypress();
} catch (Throwable $t) {
    exit($t->getMessage());
}
$i = $keypress->reader; // $i is our "input handler"
echo "You are using input method '{$keypress->methods[$keypress->method]}' with OS set to {$keypress->os}\n";
echo "Please press a key: ";
$key = $i->read();
var_dump($key);

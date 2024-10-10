<?php

require "vendor/autoload.php";
use Nahkampf\PhpKeypressWindows\Keypress;

// Initialize the keypress reader
try {
    $keypress = new Keypress(__DIR__ . "\src\windows.h");
} catch (Throwable $t) {
    exit($t->getMessage());
}

if ($keypress->method == $keypress::METHOD_READLINE) {
    // You'll have to handle Readlines readline_install_callback_handler() stuff yourself
    // Just note that the output/values from that might differ from Windows native, especially regarding meta chars
    exit("You'll have to handle readline yourself.");
} else {
    $i = $keypress->reader; // $i is our FFI "input handler"
    echo "You are using input method '{$keypress->methods[$keypress->method]}' with OS set to {$keypress->os}\n";
    echo "Please press a key: \n";
    while ($key = $i->read()) {
        var_dump($key);
    }
}

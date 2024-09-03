<?php

namespace Nahkampf\PhpKeypressWindows;

class Readline implements InputInterface
{
    public function __construct()
    {
        return true;
    }

    public function read(int $timeout = 0)
    {
        readline_callback_handler_install(
            "",
            function () {
            }
        );
        while (true) {
            $r = array(STDIN);
            $w = null;
            $e = null;
            stream_set_blocking(STDIN, false);
            $n = stream_select($r, $w, $e, $timeout);
            if ($n && in_array(STDIN, $r)) {
                $c = stream_get_contents(STDIN, 1);
                // Handle meta keys here
                $meta = stream_get_meta_data(STDIN);
                // readline suppresses tab so force insertion
                if (ord($c) == 9) {
                    echo chr(9);
                }
                if (ord($c) == 27) {
                    if ($meta['unread_bytes'] == 0) {
                        echo "ESCAPE";
                        continue;
                    }
                    $c = stream_get_contents(STDIN, $meta['unread_bytes']);
                    if ($c == "[A") { echo "UP"; }
                    if ($c == "[B") { echo "DOWN"; }
                    if ($c == "[C") { echo "RIGHT"; }
                    if ($c == "[D") { echo "LEFT"; }
                    if ($c == "[F") { echo "END"; }
                    if ($c == "[H") { echo "HOME"; }
                    if ($c == "OP") { echo "F1"; }
                    if ($c == "OQ") { echo "F2"; }
                    if ($c == "OR") { echo "F3"; }
                    if ($c == "OS") { echo "F4"; }
                    if ($c == "[15~") { echo "F5"; }
                    if ($c == "[16~") { echo "F6"; }
                    if ($c == "[17~") { echo "F7"; }
                    if ($c == "[18~") { echo "F8"; }
                }
            }
        }
        readline_callback_handler_remove();
    }
}

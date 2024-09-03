<?php

namespace Nahkampf\PhpKeypressWindows;

use Nahkampf\PhpKeypressWindows\Keypress as KP;

class Exec implements InputInterface
{
    public function __construct($method = KP::METHOD_SHELLREAD)
    {
        switch ($method) {
            case KP::METHOD_SHELLREAD:
                break;
            case KP::METHOD_WINBINARY:
                break;
            case KP::METHOD_NODE:
                break;
            default:
                throw new \Exception("Invalid input method");
                break;
        }
    }

    public function read(int $timeout)
    {

    }
}

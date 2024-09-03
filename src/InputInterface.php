<?php

namespace Nahkampf\PhpKeypressWindows;

interface InputInterface
{
    public function read(int $timeout);
}

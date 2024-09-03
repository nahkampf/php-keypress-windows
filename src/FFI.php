<?php

namespace Nahkampf\PhpKeypressWindows;

define('STD_INPUT_HANDLE', -10);
define('ENABLE_ECHO_INPUT', 0x0004);
define('ENABLE_PROCESSED_INPUT', 0x0001);
define('ENABLE_WINDOW_INPUT', 0x0008);
define('KEY_EVENT', 0x0001);

class FFI implements InputInterface
{
    public function __construct()
    {

    }

    public function read(int $timeout)
    {
        $windows = \FFI::load('../assets/windows.h');
        $handle = $windows->GetStdHandle(STD_INPUT_HANDLE);
        $oldMode = $windows->new('DWORD');
        if (!$windows->GetConsoleMode($handle, \FFI::addr($oldMode))) {
            echo "Cannot initialize old console mode!\n";
            exit;
        }
        $newConsoleMode = ENABLE_WINDOW_INPUT | ENABLE_PROCESSED_INPUT;
        if (!$windows->SetConsoleMode($handle, $newConsoleMode)) {
            echo "Impossible to change the console mode\n";
            exit;
        }
        $bufferSize = $windows->new('DWORD');
        $arrayBufferSize = 128;
        $inputBuffer = $windows->new("INPUT_RECORD[$arrayBufferSize]");
        $cNumRead = $windows->new('DWORD');
        while (true) {
            $windows->GetNumberOfConsoleInputEvents(
                $handle,
                \FFI::addr($bufferSize)
            );
            if ($bufferSize->cdata > 1) {
                if (
                    !$windows->ReadConsoleInputW(
                        $handle,
                        $inputBuffer,
                        $arrayBufferSize,
                        \FFI::addr($cNumRead)
                    )
                ) {
                    echo "Read console input failing\n";
                    exit;
                }
                for ($j = $cNumRead->cdata - 1; $j >= 0; $j--) {
                    if ($inputBuffer[$j]->EventType === KEY_EVENT) {
                        $keyEvent = $inputBuffer[$j]->Event->KeyEvent;
                        return $keyEvent;
                        exit;
                    }
                }
            }
        }
        $windows->CloseHandle($handle);
    }
}

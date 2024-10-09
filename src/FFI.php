<?php

namespace Nahkampf\PhpKeypressWindows;

define('STD_INPUT_HANDLE', -10);
define('ENABLE_ECHO_INPUT', 0x0004);
define('ENABLE_PROCESSED_INPUT', 0x0001);
define('ENABLE_WINDOW_INPUT', 0x0008);
define('KEY_EVENT', 0x0001);

class FFI
{
    public const string ERROR_OLDCONSOLE = "Cannot initialize old console mode";
    public const string ERROR_CHANGEMODE = "Impossible to change the console mode";
    public const string ERROR_READCONSOLE = "Read console input failing";

    public static function read()
    {
        $windows = \FFI::load('../assets/windows.h');
        /** @psalm-suppress UndefinedMethod */
        $handle = $windows->GetStdHandle(STD_INPUT_HANDLE);
        $oldMode = $windows->new('DWORD');
        /** @psalm-suppress UndefinedMethod */
        if (!$windows->GetConsoleMode($handle, \FFI::addr($oldMode))) {
            throw new \Error(self::ERROR_OLDCONSOLE);
            exit;
        }
        $newConsoleMode = ENABLE_WINDOW_INPUT | ENABLE_PROCESSED_INPUT;
        /** @psalm-suppress UndefinedMethod */
        if (!$windows->SetConsoleMode($handle, $newConsoleMode)) {
            throw new \Error(self::ERROR_CHANGEMODE);
            exit;
        }
        $bufferSize = $windows->new('DWORD');
        $arrayBufferSize = 128;
        $inputBuffer = $windows->new("INPUT_RECORD[$arrayBufferSize]");
        $cNumRead = $windows->new('DWORD');
        while (true) {
            /** @psalm-suppress UndefinedMethod */
            $windows->GetNumberOfConsoleInputEvents(
                $handle,
                \FFI::addr($bufferSize)
            );
            if ($bufferSize->cdata > 1) {
                if (
                    /** @psalm-suppress UndefinedMethod */
                    !$windows->ReadConsoleInputW(
                        $handle,
                        $inputBuffer,
                        $arrayBufferSize,
                        \FFI::addr($cNumRead)
                    )
                ) {
                    throw new \Error(self::ERROR_READCONSOLE);
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

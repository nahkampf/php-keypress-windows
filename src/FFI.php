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
    public const string ERROR_FFI = "FFI initialization failed";

    public ?\FFI $windows = null;

    public function __construct(string $windowsHeader = "windows.h")
    {
        $this->windows = \FFI::load($windowsHeader);
        if ($this->windows === null) {
            throw new \Error(self::ERROR_FFI);
        }
    }

    public function read()
    {
        /** @psalm-suppress UndefinedMethod */
        /** @disregard */
        $handle = $this->windows->GetStdHandle(STD_INPUT_HANDLE);
        /** @psalm-suppress UndefinedMethod */
        /** @disregard */
        $oldMode = $this->windows->new('DWORD');
        /** @psalm-suppress UndefinedMethod */
        /** @disregard */
        if (!$this->windows->GetConsoleMode($handle, \FFI::addr($oldMode))) {
            throw new \Error(self::ERROR_OLDCONSOLE);
            exit;
        }
        $newConsoleMode = ENABLE_WINDOW_INPUT | ENABLE_PROCESSED_INPUT;
        /** @psalm-suppress UndefinedMethod */
        /** @disregard */
        if (!$this->windows->SetConsoleMode($handle, $newConsoleMode)) {
            throw new \Error(self::ERROR_CHANGEMODE);
            exit;
        }
        /** @psalm-suppress UndefinedMethod */
        /** @disregard */
        $bufferSize = $this->windows->new('DWORD');
        $arrayBufferSize = 128;
        /** @psalm-suppress UndefinedMethod */
        /** @disregard */
        $inputBuffer = $this->windows->new("INPUT_RECORD[$arrayBufferSize]");
        /** @psalm-suppress UndefinedMethod */
        /** @disregard */
        $cNumRead = $this->windows->new('DWORD');
        while (true) {
            /** @psalm-suppress UndefinedMethod */
            /** @disregard */
            $this->windows->GetNumberOfConsoleInputEvents(
                $handle,
                \FFI::addr($bufferSize)
            );
            if ($bufferSize->cdata > 1) {
                if (
                    /** @psalm-suppress UndefinedMethod */
                    /** @disregard */
                    !$this->windows->ReadConsoleInputA($handle, $inputBuffer, $arrayBufferSize, \FFI::addr($cNumRead))
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
        $this->windows->CloseHandle($handle);
    }
}

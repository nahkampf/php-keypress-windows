<?php

namespace Nahkampf\PhpKeypressWindows;

use Exception;

class Keypress
{
    public const METHOD_NOMETHOD = 0;
    public const METHOD_READLINE = 1;
    public const METHOD_SHELLREAD = 2;
    public const METHOD_WINFFI = 3;
    public const METHOD_WINBINARY = 4;
    public const METHOD_NODE = 5;

    public array $methods = [
        self::METHOD_NOMETHOD => "mo input method",
        self::METHOD_READLINE => "readline",
        self::METHOD_SHELLREAD => "read command in bash",
        self::METHOD_WINFFI => "FFI against user32.dll",
        self::METHOD_WINBINARY => "compiled binary",
        self::METHOD_NODE => "nodejs",
    ];

    public $os;
    public $key = null;
    public $method = self::METHOD_NOMETHOD;

    public function __construct($method = null)
    {
        // Are we on windows or not?
        switch (PHP_OS) {
            case "WINNT":
                $this->os = "WIN";
                break;
            default:
                $this->os = "OTHER";
                break;
        }

        // autodetect which input method to use
        if (!$method || $method == self::METHOD_NOMETHOD) {
            $method = $this->detectMethod();
            if ($method == self::METHOD_NOMETHOD) {
                throw new Exception("No available input methods");
            }
        }
        switch ($method) {
            case self::METHOD_READLINE:
                $this->key = new \Nahkampf\PhpKeypressWindows\Readline();
                break;
            case self::METHOD_SHELLREAD:
            case self::METHOD_NODE:
            case self::METHOD_WINBINARY:
                    $this->key = new \Nahkampf\PhpKeypressWindows\Exec($this->method);
                break;
            case self::METHOD_WINFFI:
                $this->key = new \Nahkampf\PhpKeypressWindows\FFI();
                break;
            default:
                throw new Exception("Invalid input method provided");
                break;
        }
    }

    /**
     * Compability checker
     */
    public function detectMethod()
    {
        // Do we have access to the needed readline methods?
        if (
            function_exists('readline_callback_handler_install')
            && function_exists('readline_callback_read_char')
        ) {
            // we have a compatible readline implementation, so hacks are unneccessary
            $this->method = self::METHOD_READLINE;
            return self::METHOD_READLINE;
        }
        // If we're not on windows, we can always do a shell_exec() and check if bash and read is available
        if ($this->os == "OTHER") {
            $probe = shell_exec("/bin/bash \"read -t 0.00001 -p 'READ AVAILABLE'\"");
            if ($probe == "READ AVAILABLE") {
                $this->method = self::METHOD_SHELLREAD;
                return self::METHOD_SHELLREAD;
            }
        }
        // If we are on windows, there are two possible workarounds.
        if ($this->os == "WIN") {
            // First, let's see if we can do FFI
            if (extension_loaded('ffi')) {
                $this->method = self::METHOD_WINFFI;
                return self::METHOD_WINFFI;
            }
            // If FFI was not available, we'll try to run the precompiled binary.
            $probe = shell_exec(__DIR__ . DIRECTORY_SEPARATOR . "assets " . DIRECTORY_SEPARATOR . "keypress.exe");
            if ($probe == "BINARY AVAILABLE") {
                $this->method = self::METHOD_WINBINARY;
                return self::METHOD_WINBINARY;
            }
        }
        // Lastly, we can check if node is available and exec to it (this should work for both *nix and win)
        $probe = shell_exec("echo \"console.log('NODE AVAILABLE');\" | node");
        if ($probe == "NODE AVAILABLE") {
            $this->method = self::METHOD_NODE;
            return self::METHOD_NODE;
        }
        // no input methods were available :(
        $this->method = self::METHOD_NOMETHOD;
        return self::METHOD_NOMETHOD;
    }
}

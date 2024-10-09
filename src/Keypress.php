<?php

namespace Nahkampf\PhpKeypressWindows;

use Exception;

class Keypress
{
    public const METHOD_NOMETHOD = 0;
    public const METHOD_READLINE = 1;
    public const METHOD_WINFFI = 3;

    public const string ERROR_CLI = "This can only be used from the CLI";
    public const string ERROR_NOMETHOD = "No valid input method found";

    public string $os;
    public $reader = null;
    public $method = self::METHOD_NOMETHOD;

    public array $methods = [
        self::METHOD_READLINE => "GNU Readline",
        self::METHOD_WINFFI => "FFI using User32.dll"
    ];

    public function __construct($method = null)
    {
        // We can only do this from CLI
        if (php_sapi_name() !== "cli") {
            throw new \Error(self::ERROR_CLI);
        }
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
        $method = $this->detectMethod();
        switch ($method) {
            case self::METHOD_READLINE:
                $this->reader = new \Nahkampf\PhpKeypressWindows\Readline();
                break;
            case self::METHOD_WINFFI:
                $this->reader = new \Nahkampf\PhpKeypressWindows\FFI();
                break;
            default:
                throw new \Error(self::ERROR_NOMETHOD);
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
        // If we are on windows, use the FFI workaround (if we can)
        if ($this->os == "WIN") {
            if (extension_loaded('ffi')) {
                $this->method = self::METHOD_WINFFI;
                return self::METHOD_WINFFI;
            }
        }
        // no input methods were available :(
        $this->method = self::METHOD_NOMETHOD;
        return self::METHOD_NOMETHOD;
    }

    public function read()
    {
        $this->reader->read();
    }
}

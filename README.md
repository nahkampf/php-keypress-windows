# Keypress detection for PHP CLI under Windows using FFI
Long and boring story short: PHP for Windows uses a readline implementation that does not support [some functions](https://github.com/php/doc-en/issues/1482) needed for handling keypresses. This kind of makes it impossible to do basic TUI stuff, since you can *only* get line inputs (ie you have to press ENTER before input gets processed). Until someone decides to implement the missing functions in [WinEditLine](https://github.com/winlibs/wineditline) (or build another GNU Readline compatible library for PHP), we're stuck. 

This lib a workaround hack for that problem, and only applies if you want to have keypress detection with PHP in a windows environment. If available, this lib let's you use windows native keypress detection via FFI. If there is a capable Readline extension detected, you will have to use that instead.

I have borrowed much of this from [Nek-](https://gist.github.com/Nek-/118cc36d0d075febf614c53a48470490), thank you!


## FFI
Using [FFI](https://www.php.net/manual/en/book.ffi.php) (`PHP 7 >= 7.4.0, PHP 8`) allows us to hook into Windows native key detection.

## How to use
Include in your project and then:
```
$keypress = new Nahkampf\PhpKeypressWindows\Keypress();
$input = $keypress->reader;
$keyPressed = $input->read();
```

NOTE that if a capable Readline is detected (`$keypress->method` will be set to readline and `reader` will be `null`) you'll have to implent your own Readline stuff (or [CLIMate](https://climate.thephpleague.com/), [Symfony Console](https://symfony.com/doc/current/components/console.html) or whatever). This lib is *strictly* for getting keypress capability in PHP CLI under Windows.

See included `example.php` in this lib.

## The `windows.h` file
The header file that FFI loads contains a definition for the path to `kernel32.dll`. If, for some reason, kernel32.dll is located somewhere other than `C:\Windows\System32` you'll have to roll your own file and pass the full path of the file to `Keypress()`, e.g `$keypress = new Keypress('..\..\myWindows.h')`. Here's the contents of the windows header file:
```C
#define FFI_LIB "C:\\Windows\\System32\\kernel32.dll"
typedef unsigned short wchar_t;
typedef int BOOL;
typedef unsigned long DWORD;
typedef void *PVOID;
typedef PVOID HANDLE;
typedef DWORD *LPDWORD;
typedef unsigned short WORD;
typedef wchar_t WCHAR;
typedef short SHORT;
typedef unsigned int UINT;
typedef char CHAR;

typedef struct _COORD {
  SHORT X;
  SHORT Y;
} COORD, *PCOORD;

typedef struct _WINDOW_BUFFER_SIZE_RECORD {
  COORD dwSize;
} WINDOW_BUFFER_SIZE_RECORD;

typedef struct _MENU_EVENT_RECORD {
  UINT dwCommandId;
} MENU_EVENT_RECORD, *PMENU_EVENT_RECORD;

typedef struct _KEY_EVENT_RECORD {
  BOOL  bKeyDown;
  WORD  wRepeatCount;
  WORD  wVirtualKeyCode;
  WORD  wVirtualScanCode;
  union {
    WCHAR UnicodeChar;
    CHAR  AsciiChar;
  } uChar;
  DWORD dwControlKeyState;
} KEY_EVENT_RECORD;

typedef struct _MOUSE_EVENT_RECORD {
  COORD dwMousePosition;
  DWORD dwButtonState;
  DWORD dwControlKeyState;
  DWORD dwEventFlags;
} MOUSE_EVENT_RECORD;

typedef struct _FOCUS_EVENT_RECORD {
  BOOL bSetFocus;
} FOCUS_EVENT_RECORD;

typedef struct _INPUT_RECORD {
  WORD  EventType;
  union {
    KEY_EVENT_RECORD          KeyEvent;
    MOUSE_EVENT_RECORD        MouseEvent;
    WINDOW_BUFFER_SIZE_RECORD WindowBufferSizeEvent;
    MENU_EVENT_RECORD         MenuEvent;
    FOCUS_EVENT_RECORD        FocusEvent;
  } Event;
} INPUT_RECORD;
typedef INPUT_RECORD *PINPUT_RECORD;

// Original definition is
// WINBASEAPI HANDLE WINAPI GetStdHandle (DWORD nStdHandle);
// https://github.com/Alexpux/mingw-w64/blob/master/mingw-w64-headers/include/processenv.h#L31
HANDLE GetStdHandle(DWORD nStdHandle);

// https://docs.microsoft.com/fr-fr/windows/console/getconsolemode
BOOL GetConsoleMode(
	/* _In_ */HANDLE  hConsoleHandle,
	/* _Out_ */ LPDWORD lpMode
);

// https://docs.microsoft.com/fr-fr/windows/console/setconsolemode
BOOL SetConsoleMode(
  /* _In_ */ HANDLE hConsoleHandle,
  /* _In_ */ DWORD  dwMode
);

// https://docs.microsoft.com/fr-fr/windows/console/getnumberofconsoleinputevents
BOOL GetNumberOfConsoleInputEvents(
  /* _In_ */  HANDLE  hConsoleInput,
  /* _Out_ */ LPDWORD lpcNumberOfEvents
);

// https://docs.microsoft.com/fr-fr/windows/console/readconsoleinput
BOOL ReadConsoleInputA(
  /* _In_ */  HANDLE        hConsoleInput,
  /* _Out_ */ PINPUT_RECORD lpBuffer,
  /* _In_ */  DWORD         nLength,
  /* _Out_ */ LPDWORD       lpNumberOfEventsRead
);
BOOL ReadConsoleInputW(
  /* _In_ */  HANDLE        hConsoleInput,
  /* _Out_ */ PINPUT_RECORD lpBuffer,
  /* _In_ */  DWORD         nLength,
  /* _Out_ */ LPDWORD       lpNumberOfEventsRead
);

BOOL CloseHandle(HANDLE hObject);
```

## Key events
The interface returns a key-event array that looks something like this (when pressing CTRL):

```C
object(FFI\CData:struct _KEY_EVENT_RECORD)#11 (6) {
  ["bKeyDown"]=>
  int(1)
  ["wRepeatCount"]=>
  int(1)
  ["wVirtualKeyCode"]=>
  int(17)
  ["wVirtualScanCode"]=>
  int(29)
  ["uChar"]=>
  object(FFI\CData:union <anonymous>)#10 (2) {
    ["UnicodeChar"]=>
    int(0)
    ["AsciiChar"]=>
    string(1) ""
  }
  ["dwControlKeyState"]=>
  int(8)
}
```

## Caveats
- The implementation will return one _or_ two events, depending on how quick you are to release the key
- The behaviour of `bKeyDown` seems inconsistent, especially if keep a button pressed down for a while.
- `wRepeatCount` does not seem to work in this implementation
- You most likely want to look at `wVirtualKeyCode` (which is device-independent) rather than `wVirtualScanCode` (which can be device specific).
- Keep in mind that quite a lot of "keys" on the keyboard have no `uChar` equivalent (function keys, meta keys, delete etc). For those the `UnicodeChar` will always be `0` and `AsciiChar` will always be null/empty.
- `dwControlKeyState` is set to the Virtual Key Code for that meta key if pressed, which can be used for combinations like CTRL-A etc. Note however that pressing `<meta> + <key>` will generate two, three _or_ four key events depending on how quick your fingers are. One keydown event for each of the two keys, and one (_or_ two) for the keyup. This makes standard keysequences using CTRL/ALT modifiers a bit tricky to implement. I don't have the time or frankly the sanity points to solve this, but if anyone does PRs are more than welcome!

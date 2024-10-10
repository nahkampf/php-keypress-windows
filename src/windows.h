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
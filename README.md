# Keypress detection for PHP CLI under Windows using FFI
Long and boring story short: PHP for Windows uses a readline implementation that does not support [some functions](https://github.com/php/doc-en/issues/1482) needed for handling keypresses. This kind of makes it impossible to do basic TUI stuff, since you can *only* get line inputs (ie you have to press ENTER before input gets processed). Until someone decides to implement the missing functions in [WinEditLine](https://github.com/winlibs/wineditline) (or build another GNU Readline compatible library for PHP), we're stuck. 

This lib a workaround hack for that problem, and only applies if you want to do CLI stuff with PHP in a windows environment. This lib is safe to inlcude in tools that might also be run under linux/unix, since it will automatically detect if there's a capable Readline extension and use that instead of the FFI or Exec methods.

# Workarounds
There are two workarounds for this problem:
- Using FFI to hook into Windows built in key detection or
- Calling an executable
  
# FFI
Using [FFI](https://www.php.net/manual/en/book.ffi.php) (`PHP 7 >= 7.4.0, PHP 8`) allows us to hook into Windows `User32.dll` library and use the native key detection there. 

## Using an executable
This is the clunky option. 
We rely on an EXE file called `keypress.exe` that does one simple thing: wait for keyboard input, and output a json blob of the key data once a key was pressed. This file can then be called using `shell_exec()` in php to give a completely transparent "keypress detector". The caveat here is of course that you need to have permissions to exec files in your environment.

### Due dilligence
You should probably make a habit of not downloading and running random executables in your environment without some due dilligence, but a precompiled executable *is* included here for ease of use. But don't trust me; instead look at the code here and build your own:

### How to build
1. `node --experimental-sea-config sea-config.json`
2. `npm run build`
3. `node -e "require('fs').copyFileSync(process.execPath, 'keypress.exe')"`
4. `signtool.exe remove /s keypress.exe`
5. `npx postject keypress.exe NODE_SEA_BLOB sea-prep.blob --sentinel-fuse NODE_SEA_FUSE_fce680ab2cc467b6e072b8b5df1996b2`

## How to use
Build your own `keypress.exe` (or use the binary provided here) and stick it somewhere safe on your webserver where PHP can exec it. Then something like this should see you through (add your own try/catches and safeguards as you see fit):

```php
<?php
function readkey(): \stdClass {
    return json_decode(shell_exec('/PATH/TO/keypress.exe'));
}
```

If you were to press `ctrl+arrow-up` you should get an object back containing this:

```
stdClass Object
(
    [sequence] => \u001b[1;5A
    [name] => up
    [ctrl] => 1
    [meta] =>
    [shift] =>
    [code] => [A
)
```

## TODO
- [ ] Get someone to write a much, much smaller binary for windows (C/C++?)
- [ ] Add more flavours of keypress scripts (rust, go, python, perl, whatever is likely to exist on a random linux machine)
- [ ] Look into using `CHOICE` in windows environment as well

## Credits
This extremely simple tool is written in nodejs and is basically just ripping off a small script by [Dennis Hackethal](https://github.com/TooTallNate/keypress/issues/28). Thanks!

# Keypress Dumper (windows)
Long and boring story short: PHP for Windows uses a readline implementation that does not support [some functions](https://github.com/php/doc-en/issues/1482) needed for handling keypresses. This kind of makes it impossible to do basic TUI stuff, since you can *only* get line inputs (ie you have to press ENTER before input gets processed). Until someone decides to implement the missing functions in [WinEditLine](https://github.com/winlibs/wineditline), we're stuck. There are other workarounds of course, but much more complicated: writing your own extension to handle keypresses, or doing this via FFI (or even possibly using Direct I/O).

This is a workaround hack for that problem, and only applies if you want to do CLI stuff with PHP in a windows environment.

## The workaround
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
## Credits
This extremely simple tool is written in nodejs and is basically just ripping off a small script by [Dennis Hackethal](https://github.com/TooTallNate/keypress/issues/28). Thanks!

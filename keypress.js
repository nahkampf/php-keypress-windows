var keypress = require('keypress');
keypress(process.stdin);
process.stdin.on('keypress', function (ch, key) {
  console.log(JSON.stringify(key));
  process.stdin.pause();
});
process.stdin.setRawMode(true);
process.stdin.resume();
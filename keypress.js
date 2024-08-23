let readline = require('readline');
readline.emitKeypressEvents(process.stdin);
process.stdin.on('keypress', (ch, key) => {
  console.log(JSON.stringify(key));
  process.stdin.pause();
});
process.stdin.setRawMode(true);

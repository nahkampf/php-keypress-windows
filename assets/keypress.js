const args = process.argv;
if (args[2] == "diagnose") {
  console.log("BINARY AVAILABLE");
  process.exit()
}

let readline = require('readline');
readline.emitKeypressEvents(process.stdin);
process.stdin.on('keypress', (ch, key) => {
  console.log(JSON.stringify(key));
  process.stdin.pause();
});
process.stdin.setRawMode(true);

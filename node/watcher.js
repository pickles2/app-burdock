console.log('----- Starting CCE Watcher');
const Watcher = require('./watcher/main.js');
const watcher = new Watcher();
watcher.start();

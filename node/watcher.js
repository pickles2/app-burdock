console.log('----- Starting Async Event Watcher');
const Watcher = require('./watcher/main.js');
const watcher = new Watcher();
watcher.start();

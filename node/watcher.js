console.log('----- Starting CCE Watcher');
const CceWatcher = require('./cce/CceWatcher.js');
const cceWatcher = new CceWatcher();
cceWatcher.start();

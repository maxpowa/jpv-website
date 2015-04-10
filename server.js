#!/usr/bin/env node
'use strict';

var commandline = require('commander');
var packjson = require('./package.json');
var util = require('./lib/utils');

console.log('====== nodejs '+process.version+' - jpv-website '+packjson.version+' ======');

commandline
  .version(packjson.version)
  .option('-m, --mode [server|worker]', 'Worker updates/generates cache and server serves the website.', 'worker')
  .parse(process.argv);

if (commandline.mode === 'worker') {
  console.log('Starting jpv in worker mode, generating cache.');
  var cache = require('./lib/cache');
  var exitcode = cache.generate() || 1;
  process.exitCode = exitcode;
} else if (commandline.mode === 'server') {
  console.log('Starting jpv in server mode... It is recommended to run this with \'forever\' for best results');
  var router = require('./lib/routes');
  router.init();
} else {
  commandline.help();
}

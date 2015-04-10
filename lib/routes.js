/*jslint node: true */
'use strict';

// import our modules
var http         = require('http');
var router_gen   = require('router');
var finalhandler = require('finalhandler');
var compression  = require('compression');
var config       = require('../config');

var init = function() {
  // store our message to display
  var message = "Hello World!";

  // initialize the router & server and add a final callback.
  var router = router_gen();
  var server = http.createServer(function onRequest(req, res) {
    router(req, res, finalhandler(req, res));
  });

  // use some middleware and compress all outgoing responses
  router.use(compression());

  // handle `GET` requests to `/message`
  router.get('/', function (req, res) {
    res.statusCode = 200;
    res.setHeader('Content-Type', 'text/plain; charset=utf-8');
    res.end(message + '\n');
  });

  // make our http server listen to connections
  server.listen(config.web.port);
};

module.exports.init = init;

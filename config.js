/*jslint node: true */
'use strict';

var fs = require('fs');

var config = {};

config.media = {};
config.web = {};

config.media.regex = /\.mp3$/; // Uses javascript regex format
config.media.exclude = /^.sync/;
config.media.root = __dirname;
config.media.dir = __dirname+'/media/';
config.media.cache = __dirname+'/cache/';
config.media.thumb_px = 70;
config.media.genres = [ 'jpop', 'vocaloid', 'nightcore' ];
config.media.cache_period = 604800; // Default 604800 (7 days)

config.web.bind = undefined; // May be set to a unix socket or a specific ip address if required
config.web.port = 8192;

module.exports = config;

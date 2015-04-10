/*jslint node: true */
'use strict';

var fs = require('fs');
var walk = require('fs-walk');
var path = require('path');
var mkdirp = require('mkdirp');
var sqlite3 = require('sqlite3').verbose();

// Check if an object is a function
var isFunction = function (object) {
  return !!(object && object.constructor && object.call && object.apply);
};

var checkDirs = function (dirs) {
  dirs.forEach(function(value, index, array){
    mkdirp(value, function(err){
      if (err) return console.error(err);
    });
  });
};

// Iterate through directory and perform given action on every file
var dive = function (dir, action) {
  if (!isFunction(action)) {
    action = function (fn, stat) {
      console.log(fn);
    };
  }

  walk.filesSync(dir, function (baseDir, filename, stat, next) {
    action(path.join(baseDir, filename), stat);
  }, function (err) {
    if (err) console.error('Error: '+err);
  });
};

module.exports.dive = dive;
module.exports.isFunction = isFunction;
module.exports.checkDirs = checkDirs;

// Sqlite3 functions

var createdb = function (db_file) {
  var db = new sqlite3.Database(db_file);

  db.serialize(function() {
    db.run('CREATE TABLE IF NOT EXISTS tags (hash VARCHAR(32) PRIMARY KEY, filename TEXT UNIQUE, ' +
           'bitrate INTEGER, size INTEGER, bitrate_mode TEXT, album TEXT, album_artist TEXT, ' +
           'artist TEXT, genre TEXT, genre_folder TEXT, title TEXT, length TEXT, href TEXT)');
  });
};

module.exports.createdb = createdb;

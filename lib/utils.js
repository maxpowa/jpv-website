/*jslint node: true */
'use strict';

var fs = require('fs');
var walk = require('fs-walk');
var path = require('path');
var mkdirp = require('mkdirp');
var sqlite3 = require('sqlite3').cached;

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

  walk.filesSync(dir, function (baseDir, filename, stat) {
    action(path.join(baseDir, filename), stat);
  }, function (err) {
    if (err) console.error('Error: '+err);
  });
};

module.exports.dive = dive;
module.exports.isFunction = isFunction;
module.exports.checkDirs = checkDirs;

// Sqlite3 functions

var createdb = function (db_fn) {
  var db = new sqlite3.Database(db_fn);

  db.serialize(function() {
    db.run('CREATE TABLE IF NOT EXISTS tags (hash VARCHAR(32) PRIMARY KEY, filename TEXT UNIQUE, ' +
           'bitrate INTEGER, size INTEGER, bitrate_mode TEXT, album TEXT, album_artist TEXT, ' +
           'artist TEXT, genre TEXT, genre_folder TEXT, title TEXT, length TEXT, href TEXT)');
  });
};

var listGenre = function (db_fn, genre, func) {
    var db = new sqlite3.Database(db_fn);

    db.serialize(function() {
      db.each('SELECT * FROM tags WHERE genre_folder = ? COLLATE NOCASE ORDER BY artist, title ASC',
        genre, func);
    });
};

var clearGenre = function (db_fn, genre) {
  var db = new sqlite3.Database(db_fn);

  db.serialize(function() {
    var stmt = db.prepare('DELETE FROM tags WHERE genre_folder = ? COLLATE NOCASE');
    stmt.run(genre);
    stmt.finalize();
  });
};

var countGenreFiles = function (db_fn, genre) {

};

var getFilenames = function (db_fn, genre) {

};

var removeFile = function (db_fn, fn, genre) {

};

var searchFiles = function (db_fn, query) {

};

var getAllFiles = function (db_fn) {

};

var getTags = function (db_fn, fn, func) {
  getTagsWId(db_fn, fn, fs.stat(fn).ino);
};

var getTagsWId = function (db_fn, fn, id, func) {
  var db = new sqlite3.Database(db_fn);

  db.serialize(function() {
    //db.each('SELECT * FROM tags WHERE hash = ?',id,
    db.each('SELECT * FROM tags',
      function(err, row) {
        func(err, row);
      });
  });
};

module.exports.createdb = createdb;
module.exports.listGenre = listGenre;
module.exports.clearGenre = clearGenre;
module.exports.countGenreFiles = countGenreFiles;
module.exports.getFilenames = getFilenames;
module.exports.removeFile = removeFile;
module.exports.searchFiles = searchFiles;
module.exports.getAllFiles = getAllFiles;
module.exports.getTags = getTags;
module.exports.getTagsWId = getTagsWId;

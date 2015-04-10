/*jslint node: true */
'use strict';

var fs = require('fs');
var lwip = require('lwip');
var crypto = require('crypto');
var mm = require('musicmetadata');
var sqlite3 = require('sqlite3').verbose();
var util = require('./utils');
var config = require('../config');

//console.log(crypto.getHashes());
var db_fn = config.media.cache+'/tags.db';

var generate = function() {
  // Make sure some crucial directories exist.
  util.checkDirs([config.media.dir, config.media.cache, config.media.cache+'/img/']);

  // Store tags in the database
  util.createdb(db_fn);

  // Dive into the media directory and start gathering id3 tags.
  util.dive(config.media.dir, function (path, stat) {
    var path_norm = path.replace(config.media.dir,'');
    // Only process the file if it matches the regex in the config
    if (path_norm.match(config.media.regex) !== null &&
        path_norm.match(config.media.exclude) === null) {

        getId3Tags(path, path_norm, stat.ino);

    } else {
      console.log('Skipping %s', path_norm);
    }
  });
};

function getId3Tags(path, path_norm, id, art) {
  if (art === undefined)
    art = false;
  var stream = fs.createReadStream(path);
  // musicmetadata load, is asynchronous alongside the md5
  mm(stream, {duration: true}, function (err, meta) {
    console.log('Processing \'%s\' (%s).', path_norm, id);
    // Return from musicmetadata handling if there's an error
    if (err) return console.error(err);

    if (!art) {

      util.getTagsWId(db_fn, path, id, function (err, row) {
        console.error(err);
        console.log(row);
      });
      console.log('Dispatched SQL');

    }

    if (meta && art && meta.picture && meta.picture[0]) {
      // Load the album art into a lwip image
      try {
        lwip.open(meta.picture[0].data, meta.picture[0].format, function(err, image) {
          // Return and skip thumbnail generation if there's an error
          if (err) return console.error(err);

          // Coerce the album art into a configurable size thumbnail
          image.cover(config.media.thumb_px, config.media.thumb_px, function(err, image) {
            // If there's an error coercing the thumbnail into the proper size, abort.
            if (err) return console.error(err);

            var img_file = config.media.cache+'/img/'+id+'.jpg';
            fs.stat(img_file, function(err, stat) {
              if (stat && stat.isFile())
                return;
              image.writeFile(img_file, function(err){
                  // How did you even? Show the error and do return.
                  if (err) return console.error(err);
              });
            });
          });
        });
      } catch (ex) {
        console.error('Error caching art for \'%s\'', ex);
      }
    }
  });
}

module.exports.generate = generate;

/*jslint node: true */
'use strict';

var fs = require('fs');
var lwip = require('lwip');
var crypto = require('crypto');
var mm = require('musicmetadata');
var sqlite3 = require('sqlite3').verbose();
var util = require('./utils');
var config = require('../config');

var generate = function() {
  // Make sure some crucial directories exist.
  util.checkDirs([config.media.dir, config.media.cache, config.media.cache+'/img/']);

  // Dive into the media directory and start gathering id3 tags.
  util.dive(config.media.dir, function (path, stat) {

    // Only process the file if it matches the regex in the config
    if (path.match(config.media.regex) !== null) {

      // We will calculate md5 at the same time as reading id3 tags...
      // Not exactly the safest way, but it's more efficient than going over the file twice.
      var hash = crypto.createHash('md5'),
          stream = fs.createReadStream(path),
          md5 = '';

      // Subscribe to the 'data' event, to update the md5
      stream.on('data', function(data) {
        hash.update(data, 'utf8');
      });

      // Subscribe to 'end' event, to set the md5
      stream.on('end', function() {
        md5 = hash.digest('hex');
      });

      // musicmetadata load, is asynchronous alongside the md5
      mm(stream, {duration: true}, function (err, meta) {
        console.log('Processing \'%s\'.', path.replace(config.media.dir, ''));
        // Return from musicmetadata handling if there's an error
        if (err) return console.error(err);

        // Store tags in the database
        util.createdb(config.media.cache+'/tags.db')


        if (meta && meta.picture && meta.picture[0]) {
          // Load the album art into a lwip image
          lwip.open(meta.picture[0].data, meta.picture[0].format, function(err, image) {
            // Return and skip thumbnail generation if there's an error
            if (err) return console.error(err);
            var file = config.media.cache+'/img/'+md5+'.jpg';

            // Coerce the album art into a configurable size thumbnail
            image.cover(config.media.thumb_px, config.media.thumb_px, function(err, image) {
              // If there's an error coercing the thumbnail into the proper size, abort.
              if (err) return console.error(err);

              image.writeFile(file, function(err){
                  // How did you even? Show the error and do return.
                  if (err) return console.error(err);
              });
            });
          });
        }
      });
    }
  });
};

module.exports.generate = generate;

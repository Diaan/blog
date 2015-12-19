(function() {
  'use strict';

  module.exports = {
    server: [
      'sass:server'
    ],
    test: [],
    dist: [
      'sass',
      'imagemin',
      'svgmin'
    ]
  };
})();

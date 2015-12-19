(function() {
  'use strict';

  angular
    .module('admin.mock.service.blogapi', [])
    .factory('BlogapiService', BlogapiService);

  function BlogapiService() {
    return {
      some: someSpy
    };
  }

  var someSpy = jasmine.createSpy().and.returnValue(
    function(cb) {
      return result;
    }
  );

  var result = {};
})();

(function () {
  'use strict';

  describe('Service: BlogapiService', function() {

    var BlogapiService, $rootScope;

    beforeEach(module('admin.service.blogapi'));

    beforeEach(inject(function (_$rootScope_, _BlogapiService_) {
      BlogapiService = _BlogapiService_;
      $rootScope = _$rootScope_;
    }));

    describe('someThing', function() {
      it('someThing', function() {

      });
    });
  });
})();

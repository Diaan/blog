(function() {
  'use strict';

  describe('Controller: FeedController', function() {

    beforeEach(module('admin.components.feed'));

    var FeedController;

    beforeEach(inject(function($controller) {
      FeedController = $controller('FeedController');
    }));

    describe('FeedController', function() {
      it('Test Case', function() {
        FeedController.activate();
      });
    });
  });
})();

(function() {
  'use strict';

  describe('Controller: QueueController', function() {

    beforeEach(module('admin.components.queue'));

    var QueueController;

    beforeEach(inject(function($controller) {
      QueueController = $controller('QueueController');
    }));

    describe('QueueController', function() {
      it('Test Case', function() {
        QueueController.activate();
      });
    });
  });
})();

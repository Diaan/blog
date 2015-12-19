(function() {
  'use strict';

  describe('Controller: ContactController', function () {

    beforeEach(module('admin.components.contact'));

    var ContactController;

    beforeEach(inject(function ($controller) {
      ContactController = $controller('ContactController');
    }));

    describe('ContactController', function() {
      it('Test Case', function () {
        ContactController.activate();
      });
    });
  });
})();

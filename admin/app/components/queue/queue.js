/**
 * Queue Components module.
 *
 * @module admin.components.queue
 */
(function () {
  'use strict';

  angular
    .module('admin.components.queue', [
      'admin.service.blogapi'
    ])
    .controller('QueueController', QueueController);

  QueueController.$inject = ['BlogapiService'];

  /**
   * QueueController
   *
   * @class QueueController
   * @constructor
   */
  function QueueController(BlogapiService) {
    console.log('QueueController Constructor');
    this.BlogapiService = BlogapiService;
    BlogapiService.setAuth('e6f5b0e9b117ce0ca0c1ee3795993948');

    this.filterOptions = [
        {type : ''},
        {type : 'blogger'},
        {type : 'flickr'},
        {type : 'instagram'},
        {type : 'location'},
        {type : 'soundcloud'},
        {type : 'twitter'},
        {type : 'youtube'}
      ];
  }

  /**
   * The controller canActivate makes it convenient to re-use the logic
   * for a refresh for the controller/View, keeps the logic together.
   *
   * @method canActivate
   */
  QueueController.prototype.canActivate = function() {
    console.log('QueueController canActivate Method');
    return true;
  };

  /**
   * The controller activate makes it convenient to re-use the logic
   * for a refresh for the controller/View, keeps the logic together.
   *
   * @method activate
   */
  QueueController.prototype.activate = function() {
    console.log('QueueController activate Method');
    vm = this;
    var call = this.BlogapiService.queue.get().$promise;
    call
      .then(setQueue)
      .catch(error);
  };

  /**
   * The controller canDeactivate makes it convenient to re-use the logic
   * for a refresh for the controller/View, keeps the logic together.
   *
   * @method canDeactivate
   */
  QueueController.prototype.canDeactivate = function() {
    console.log('QueueController canDeactivate Method');
    return true;
  };

  /**
   * The controller deactivate makes it convenient to re-use the logic
   * for a refresh for the controller/View, keeps the logic together.
   *
   * @method deactivate
   */
  QueueController.prototype.deactivate = function() {
    console.log('QueueController deactivate Method');
    vm = this;
  };

  /**
   * Angular ViewModel
   *
   * @property vm
   * @type {Object}
   */
  var vm;

  QueueController.prototype.process = function (entry) {
    var call = this.BlogapiService.process.get({'id':entry.id}).$promise;
    call
      .then(function (data) {
        if (data.changedRows > 0) {
          entry.processed = 1;
        }
      })
      .catch(error);
  };

  QueueController.prototype.info = function (entry) {
    var call = this.BlogapiService.info.get({'id':entry.id}).$promise;
    call
      .then(function (data) {
        vm.sourceInfo = data.info;
      })
      .catch(error);
  };

  QueueController.prototype.postItemToQueue = function (post) {
    post.type = post.sourcetype.type;
    var newPost = new this.BlogapiService.queue(post);
    newPost.$save();
  };

  QueueController.prototype.delete = function (entry) {
    this.BlogapiService.queue.delete({id: entry.id}, function() {
      console.log('Deleted from server');
      vm.queue.splice(vm.queue.indexOf(entry),1);
    });
  };

  /**
   * Setting the retrieved Gruntfile list to ViewModel
   *
   * @method setlist
   * @param {Object} list Gruntfile list
   * @private
   */
  var setQueue = function (queue) {
    vm.queue = queue.data;
  };

  /**
   * It will capture the error at the time of Gruntfile data acquisition
   *
   * @method error
   * @param {Object} e error message
   * @private
   */
  var error = function (e) {
    vm.error = e;
  };
})();

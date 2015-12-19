/**
 * Feed Components module.
 *
 * @module admin.components.feed
 */
(function () {
  'use strict';

  angular
    .module('admin.components.feed', [
      'admin.service.blogapi'
    ])
    .controller('FeedController', FeedController);

  FeedController.$inject = ['BlogapiService'];

  /**
   * FeedController
   *
   * @class FeedController
   * @constructor
   */
  function FeedController(BlogapiService) {
    console.log('FeedController Constructor');
    this.BlogapiService = BlogapiService;

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
  FeedController.prototype.canActivate = function() {
    console.log('FeedController canActivate Method');
    return true;
  };

  /**
   * The controller activate makes it convenient to re-use the logic
   * for a refresh for the controller/View, keeps the logic together.
   *
   * @method activate
   */
  FeedController.prototype.activate = function() {
    console.log('FeedController activate Method');
    vm = this;
    var call = this.BlogapiService.feed.get().$promise;
    call
      .then(setFeed)
      .catch(error);
  };

  /**
   * The controller canDeactivate makes it convenient to re-use the logic
   * for a refresh for the controller/View, keeps the logic together.
   *
   * @method canDeactivate
   */
  FeedController.prototype.canDeactivate = function() {
    console.log('FeedController canDeactivate Method');
    return true;
  };

  /**
   * The controller deactivate makes it convenient to re-use the logic
   * for a refresh for the controller/View, keeps the logic together.
   *
   * @method deactivate
   */
  FeedController.prototype.deactivate = function() {
    console.log('FeedController deactivate Method');
    vm = this;
  };

  /**
   * Angular ViewModel
   *
   * @property vm
   * @type {Object}
   */
  var vm;

  FeedController.prototype.process = function (entry) {
    var call = this.BlogapiService.process.get({'id':entry.id}).$promise;
    call
      .then(function (data) {
        if (data.changedRows > 0) {
          entry.processed = 1;
        }
      })
      .catch(error);
  };

  FeedController.prototype.info = function (entry) {
    var call = this.BlogapiService.info.get({'id':entry.id}).$promise;
    call
      .then(function (data) {
        vm.sourceInfo = data.info;
      })
      .catch(error);
  };

  FeedController.prototype.delete = function (entry) {
    console.log(entry);
    this.BlogapiService.feed.delete({'id': entry.id}, function() {
      console.log('Deleted from server');
      vm.feed.splice(vm.feed.indexOf(entry),1);
    });
  };

  /**
   * Setting the retrieved Gruntfile list to ViewModel
   *
   * @method setlist
   * @param {Object} list Gruntfile list
   * @private
   */
  var setFeed = function (feed) {
    vm.feed = feed.data;
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

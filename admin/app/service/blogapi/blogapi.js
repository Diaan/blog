/**
 * Blogapi Service module.
 *
 * @module admin.service.blogapi
 */
(function () {
  'use strict';

  angular
    .module('admin.service.blogapi', [
      'ngResource'
    ])
    .factory('BlogapiService', BlogapiService);

  BlogapiService.$inject = ['$resource'];

  /**
   * @class BlogapiService
   * @constructor
   */
  function BlogapiService($resource) {
    var blogapiService = {};
    blogapiService.auth = '';
    blogapiService['setAuth'] = function(key) {
      console.log(this,key);
      blogapiService.auth = key;
    };
    blogapiService.auth = 'e6f5b0e9b117ce0ca0c1ee3795993948';
    //var apiUrl = 'http://speeltuin.dianabroeders.nl/blog/api/';
    var apiUrl = 'http://localhost/~diana/blog/api/';
    blogapiService.queue = $resource(apiUrl + 'queue/:id',{
      queueId:'@id'
    },{
      get:{
        method: 'GET',
        headers: {'Authorization': blogapiService.auth}//blogapiService.auth}
      }
    }
    );
    blogapiService.process = $resource(apiUrl + 'process/:id', {
    });

    blogapiService.info = $resource(apiUrl + 'info/:id', {
    });

    blogapiService.feed = $resource(apiUrl + 'feed/:id', {
      feedId:'@id'
    },{
      get:{
        method: 'GET',
        headers: {'Authorization': blogapiService.auth}//blogapiService.auth}
      }
    });

    return blogapiService;
  }

})();

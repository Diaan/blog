/**
 * This is admin module.
 *
 * @module admin
 */
(function () {
  'use strict';

  angular
    .module('admin', [
      'ngNewRouter',
      'admin.config',
      'admin.components.home',
      'admin.components.queue',
      'admin.components.feed'
    ])
    .controller('AppController', AppController)
    .config(['$httpProvider', function ($httpProvider) {
      //Reset headers to avoid OPTIONS request (aka preflight)
      $httpProvider.defaults.headers.common = {};
      $httpProvider.defaults.headers.post = {};
      $httpProvider.defaults.headers.put = {};
      $httpProvider.defaults.headers.patch = {};
      $httpProvider.defaults.headers.delete = {};
    }]);

  AppController.$routeConfig = [
    {path: '/',       redirectTo: '/home'},
    {path: '/home',    component: 'home'},
    {path: '/queue',   component: 'queue'},
    {path: '/feed',   component: 'feed'}
  ];

  AppController.$inject = [];

  /**
   * AppController
   *
   * @class AppController
   * @main admin
   * @constructor
   */
  function AppController () {}
  $('[data-toggle="tooltip"]').tooltip();
})();

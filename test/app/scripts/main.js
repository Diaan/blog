/**
 * This is test module.
 *
 * @module test
 */
(function () {
  'use strict';

  angular
    .module('test', [
      'ngNewRouter',
      'test.config',
      'test.components.home',
      'test.components.about',
      'test.components.contact'
    ])
    .controller('AppController', AppController);

  AppController.$routeConfig = [
    {path: '/',       redirectTo: '/home'},
    {path: '/home',    component: 'home'},
    {path: '/about',   component: 'about'},
    {path: '/contact', component: 'contact'}
  ];

  AppController.$inject = [];

  /**
   * AppController
   *
   * @class AppController
   * @main test
   * @constructor
   */
  function AppController () {}
})();

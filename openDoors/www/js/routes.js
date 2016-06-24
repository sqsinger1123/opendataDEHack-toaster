angular.module('app.routes', [])

.config(function($stateProvider, $urlRouterProvider) {

  // Ionic uses AngularUI Router which uses the concept of states
  // Learn more here: https://github.com/angular-ui/ui-router
  // Set up the various states which the app can be in.
  // Each state's controller can be found in controllers.js
  $stateProvider
    
  

      .state('menu', {
    url: '/side-menu21',
    templateUrl: 'templates/menu.html',
    abstract:true
  })

  .state('splashSignin', {
    url: '/page5',
    templateUrl: 'templates/splashSignin.html',
    controller: 'splashSigninCtrl'
  })

  .state('signup', {
    url: '/page6',
    templateUrl: 'templates/signup.html',
    controller: 'signupCtrl'
  })

  .state('marketplace', {
    url: '/page7',
    templateUrl: 'templates/marketplace.html',
    controller: 'marketplaceCtrl'
  })

  .state('menu.bidStatus', {
    url: '/page8',
    views: {
      'side-menu21': {
        templateUrl: 'templates/bidStatus.html',
        controller: 'bidStatusCtrl'
      }
    }
  })

  .state('placeBid', {
    url: '/page10',
    templateUrl: 'templates/placeBid.html',
    controller: 'placeBidCtrl'
  })

  .state('login', {
    url: '/page11',
    templateUrl: 'templates/login.html',
    controller: 'loginCtrl'
  })

  .state('menu.account', {
    url: '/page12',
    views: {
      'side-menu21': {
        templateUrl: 'templates/account.html',
        controller: 'accountCtrl'
      }
    }
  })

$urlRouterProvider.otherwise('/page5')

  

});
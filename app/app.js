var sharesApp = angular.module('sharesApp', ['ngRoute', 'LocalStorageModule']);

sharesApp.config(['$routeProvider', '$locationProvider',
    function ($routeProvider, $locationProvider) {
        $routeProvider.
            when('/', {
                templateUrl: 'app/partials/home.html',
                navItem:     ''
            }).
            when('/changelog', {
                templateUrl: 'app/partials/changelog.html',
                navItem:     'Changelog'
            }).
            when('/most-searched', {
                templateUrl: 'app/partials/most_searched.html',
                navItem:     'Most searched domains'
            }).
            when('/last-searched', {
                templateUrl: 'app/partials/last_searched.html',
                navItem:     'Last searched domains'
            }).
            when('/api-docs', {
                templateUrl: 'app/partials/api-docs.html',
                navItem:     'API'
            }).
            otherwise({
                redirectTo: '/'
            });

        $locationProvider.html5Mode(true).hashPrefix('!');
    }]);

sharesApp.config(function (localStorageServiceProvider) {
    localStorageServiceProvider.setStorageType('sessionStorage');
});

sharesApp.run(function ($rootScope) {
    $rootScope.errorReport = function ($scope, $window, errorString, errorCode) {
        $scope.error = errorString;

        $window.ga('send', 'event', 'feedback', 'error', errorCode);
    };
});

sharesApp.config(['$compileProvider', function ($compileProvider) {
    // disable debug info
    $compileProvider.debugInfoEnabled(false);
}]);
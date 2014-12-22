sharesApp.controller('LastSearchedController', ['$scope', '$http', '$window', 'sharesCache', function($scope, $http, $window, sharesCache) {
    $scope.lastSearched = false;

    $scope.visible = function() {
        // check if controller is visible on page (RWD) and do not execute code if hidden
        return angular.element('[ng-controller="LastSearchedController"]:visible').length;
    };

    var timestamp = Math.floor(Date.now() / 1000);
    var cachedData = sharesCache.get('/api/last_searched');
    var cachedDataTime = sharesCache.get('/api/last_searched-date');
    var cacheExpired = (cachedDataTime + 10) < timestamp;

    function getLastSearched() {
        if (cachedData && !cacheExpired) {
            $scope.lastSearched = cachedData;
        } else {
            $http.get('/api/last_searched').
                success(function (data, status, headers, config) {
                    if (data.status == 'OK') {
                        $scope.lastSearched = [];
                        angular.forEach(data.result, function (value, key) {
                            value.urlNoScheme = value.url.replace(/^(\w+:\/\/)/, '');
                            $scope.lastSearched.push(value);
                        });

                        sharesCache.put('/api/last_searched', $scope.lastSearched);
                        sharesCache.put('/api/last_searched-date', timestamp);
                    }
                }).
                error(function (data, status, headers, config) {
                    $scope.errorReport($scope, $window, 'Could not fetch data. Please, try again.', 9);
                });
        }
    }
    $scope.getLastSearched = getLastSearched;

    // catch event
    $scope.$on('getShares.success', function() {
        if ($scope.visible()) {
            getLastSearched();
        }
    });

}]);
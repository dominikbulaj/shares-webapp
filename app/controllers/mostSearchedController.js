sharesApp.controller('MostSearchedController', ['$scope', '$http', '$window', 'localStorageService', 'sharesCache', function($scope, $http, $window, localStorageService, sharesCache) {
    $scope.mostSearched = [];
    $scope.error = '';

    var timestamp = Math.floor(Date.now() / 1000);
    var cachedData = sharesCache.get('/api/most_searched');
    var cachedDataTime = sharesCache.get('/api/most_searched-date');
    var cacheExpired = (cachedDataTime + 60) < timestamp;

    if (cachedData && !cacheExpired) {
        $scope.mostSearched = cachedData;
    } else {
        $http.get('/api/most_searched').
            success(function (data, status, headers, config) {
                if (data.status == 'OK') {
                    $scope.mostSearched = data.result;
                    sharesCache.put('/api/most_searched', data.result);
                    sharesCache.put('/api/most_searched-date', timestamp);
                }
            }).
            error(function (data, status, headers, config) {
                $scope.errorReport($scope, $window, 'Could not fetch data. Please, try again.', 8);
            });
    }

    $scope.hideCacheNotice = function() {
        localStorageService.set('hiddenCacheNotice.MS', true);
    }
    $scope.showCacheNotice = function() {
        return !localStorageService.get('hiddenCacheNotice.MS');
    }
}]);
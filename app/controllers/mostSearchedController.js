sharesApp.controller('MostSearchedController', ['$scope', '$http', '$window', 'localStorageService', function($scope, $http, $window, localStorageService) {
    $scope.mostSearched = [];
    $scope.error = '';

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

    $scope.hideCacheNotice = function() {
        localStorageService.set('hiddenCacheNotice.MS', true);
    }
    $scope.showCacheNotice = function() {
        return !localStorageService.get('hiddenCacheNotice.MS');
    }
}]);
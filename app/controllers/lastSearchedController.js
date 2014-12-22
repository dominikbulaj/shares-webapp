sharesApp.controller('LastSearchedController', ['$scope', '$http', '$window', function($scope, $http, $window) {
    $scope.lastSearched = false;

    $scope.visible = function() {
        // check if controller is visible on page (RWD) and do not execute code if hidden
        return angular.element('[ng-controller="LastSearchedController"]:visible').length;
    };

    function getLastSearched() {
        $http.get('/api/last_searched').
            success(function (data, status, headers, config) {
                if (data.status == 'OK') {
                    $scope.lastSearched = [];
                    angular.forEach(data.result, function (value, key) {
                        value.urlNoScheme = value.url.replace(/^(\w+:\/\/)/, '');
                        $scope.lastSearched.push(value);
                    });
                }
            }).
            error(function (data, status, headers, config) {
                $scope.errorReport($scope, $window, 'Could not fetch data. Please, try again.', 9);
            });
    }
    $scope.getLastSearched = getLastSearched;

    // catch event
    $scope.$on('getShares.success', function() {
        if ($scope.visible()) {
            getLastSearched();
        }
    });

}]);
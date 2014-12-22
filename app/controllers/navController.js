sharesApp.controller('NavController', ['$scope', '$route', function($scope) {
    $scope.navItem = '';

    $scope.$on('$routeChangeSuccess', function(event, $route) {
        $scope.navItem = $route.$$route.navItem;
    })
}]);
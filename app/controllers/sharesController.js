sharesApp.controller('SharesController', ['$scope', '$rootScope', '$http', '$window', 'localStorageService', function ($scope, $rootScope, $http, $window, localStorageService) {

    $scope.url = '';
    $scope.error = '';
    $scope.results = false;
    $scope.total = 0;
    $scope.resultsUrl = '';
    $scope.loading = false;
    $scope.sort = '-shares';

    if (typeof addthis == 'object' && typeof addthis.layers == 'function') {
        //addthis.layers.refresh();
    }

    var _iconMapper = {
        'facebook':      '/assets/pics/64-facebook.png',
        'twitter':       '/assets/pics/64-twitter.png',
        'google_plus':   '/assets/pics/64-googleplus.png',
        'pinterest':     '/assets/pics/64-pinterest.png',
        'linkedin':      '/assets/pics/64-linkedin.png',
        'stumbleupon':   '/assets/pics/64-stumbleupon.png'
    };
    var _nameMapper = {
        'facebook':      'Facebook',
        'twitter':       'Twitter',
        'google_plus':   'Google+',
        'pinterest':     'Pinterest',
        'linkedin':      'LinkedIn',
        'stumbleupon':   'StumbleUpon'
    };

    $scope.getShares = function(){

        if ($.trim($scope.url) == '') {
            $scope.errorReport($scope, $window, 'Please provide URL address', 1);
            return false;
        }

        $scope.error = '';
        $scope.loading = true;

        $http({
            method: 'GET',
            url: '/api/shares',
            params: {url: $scope.url}
        }).
        success(function (data) {

            $scope.loading = false;

            if (data.status == 'OK') {
                $scope.results = [];
                $scope.total = 0;
                    angular.forEach(data.result, function(value, key){
                    $scope.results.push({network:key, shares:value, icon:_iconMapper[key], name:_nameMapper[key]});
                    $scope.total += value;
                });

                // copy url to result otherwise when writing new URL it will update text in result
                $scope.resultsUrl = $scope.url;

                // broadcast event
                $rootScope.$broadcast('getShares.success');

                // count URL
                $http({
                    method: 'POST',
                    url: '/api/count',
                    data: {url: $scope.url}
                });

            } else {
                $scope.errorReport($scope, $window, 'Could not fetch data. Please, try again.', 7);
            }

        }).
        error(function () {
            $scope.loading = false;
            $scope.errorReport($scope, $window, 'Could not fetch data. Please, try again.', 1);
        });
    }

    $scope.hideCacheNotice = function() {
        localStorageService.set('hiddenCacheNotice', true);
    }
    $scope.showCacheNotice = function() {
        return !localStorageService.get('hiddenCacheNotice');
    }


}]);
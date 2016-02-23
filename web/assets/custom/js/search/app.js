var app = angular.module('app', [],
    function ($interpolateProvider) {
        $interpolateProvider.startSymbol('[[');
        $interpolateProvider.endSymbol(']]');
    });


app.controller('resultsCtrl', ['$http', '$rootScope', '$scope', function ($http, $rootScope, $scope) {

    $scope.results = [];

    var request = {
        method: 'GET',
        url: 'results.json',
        params: {
            start: 0,
            limit: 100,
            q: $scope.q,
            facet: true
        }
    };

    getResults(request);

    $scope.$on('filters', function (event, filters) {
        delete request.params.facet;
        request.params.filters = filters;
        request.params.start = 0;
        getResults(request);
    });

    function getResults(request) {
        $http(request).then(function (response) {
            var data = response.data;
            if (data.status) {
                $scope.results = data.data;
                request.params.start = request.params.start + request.params.limit;
                if (request.params.facet) {
                    $rootScope.$broadcast('columns', data.facets);
                }
            }
        }, function (response) {
            console.debug(response);
        });
    }
}]).controller('facetsCtrl', ['$rootScope', '$scope', function ($rootScope, $scope) {
    $scope.columns = [];
    $scope.filters = {};

    $scope.$on('columns', function (event, columns) {
        $scope.columns = columns;
    });

    $scope.toggleFilter = function (column, facet) {
        if (!$scope.filters[column]) {
            $scope.filters[column] = [];
        }
        var index = $scope.filters[column].indexOf(facet);

        if (index !== -1) {
            $scope.filters[column].splice(index, 1);
            if ($scope.filters[column].length == 0) {
                delete $scope.filters[column];
            }
        } else {
            $scope.filters[column].push(facet);
        }
        $rootScope.$broadcast('filters', $scope.filters);
    };
}]);
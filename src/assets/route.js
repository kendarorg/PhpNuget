
app.controller('homeController', [
	function(){
		window.document.title = "Home";
	}
]);

app.config(['$stateProvider', '$urlRouterProvider','pathHelperProvider',
function($stateProvider, $urlRouterProvider,pathHelper) {
    $urlRouterProvider.otherwise('/');
	$stateProvider.state('root', {
            url: '/',
            templateUrl: pathHelper.buildViewsPath('home/home.html'),
			controller : "homeController"
        })
	$stateProvider.state('users', {
            url: '/admin/users',
            templateUrl: pathHelper.buildViewsPath('users/home.php')
        })
	$stateProvider.state('users_add', {
            url: '/admin/users/add',
            templateUrl: pathHelper.buildViewsPath('users/add.php')
        })
	$stateProvider.state('users_detail', {
            url: '/admin/users/:userId',
            templateUrl: pathHelper.buildViewsPath('users/detail.php'),
			controller: function($scope, $stateParams) {
				$scope.UserId = $stateParams.userId;
			}
        })
    
	$stateProvider.state('profile', {
			abstract: true,
            url: '/profile/:userId',
            templateUrl: pathHelper.buildViewsPath('profile/home.php'),
			controller: function($scope, $stateParams) {
				$scope.UserId = $stateParams.userId;
			}
        })
	$stateProvider.state('profile.user', {
            url: '/user',
            templateUrl: pathHelper.buildViewsPath('profile/profile.user.php'),
			controller: function($scope, $stateParams) {
				$scope.UserId = $stateParams.userId;
			}
        })
    
	$stateProvider.state('profile.upload', {
            url: '/upload',
            templateUrl: pathHelper.buildViewsPath('profile/profile.upload.php'),
			controller: function($scope, $stateParams) {
				$scope.UserId = $stateParams.userId;
			}
        })
	$stateProvider.state('profile.packages', {
		abstract: true,
		url: '/packages',
		templateUrl: pathHelper.buildViewsPath('profile/profile.packages.php'),
		controller: function($scope, $stateParams) {
			$scope.UserId = $stateParams.userId;
		}
	})
    
	$stateProvider.state('profile.packages.list', {
            url: '/list/:skip',
            templateUrl: pathHelper.buildViewsPath('profile/profile.packages.list.php'),
			controller: function($scope, $stateParams) {
				$scope.UserId = $stateParams.userId;
				$scope.Skip = parseInt($stateParams.skip);
			}
        })
    
	$stateProvider.state('profile.packages.detail', {
            url: '/:packageId/:packageVersion',
            templateUrl: pathHelper.buildViewsPath('profile/profile.packages.detail.php'),
			controller: function($scope, $stateParams) {
				$scope.UserId = $stateParams.userId;
				$scope.PackageId = $stateParams.packageId;
				$scope.PackageVersion = $stateParams.packageVersion;
			}
        })
}]);
				
	
app.controller('tabController', ['$scope','$location', 
	function($scope,$location){
		$scope.activeTab = 0;
		
		$scope.onClickTab = function(activeTab){
			$scope.activeTab = activeTab;
		};
		
		$scope.getActive = function(val,partialPath){
			if($location.path().indexOf(partialPath)>=0) {
				$scope.activeTab = val;
			}
			if(val == $scope.activeTab) return "active";
			return "";
		}
	}
]);
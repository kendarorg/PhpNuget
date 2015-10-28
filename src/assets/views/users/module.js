app.factory('usersService',['$http','pathHelper',
	function($http,pathHelper) {
		return {
			apiBase : pathHelper.buildApiPath('users'),
			http :$http,
			
			getAll : function() {
				return this.http.get(this.apiBase+'/?method=get');
			},
			getById : function(userId) {
				return this.http.get(this.apiBase+'/?method=getsingle&UserId='+userId);
			},
			update : function(user,userId) {
				return this.http.post(this.apiBase+'/?method=put&UserId='+userId,user);
			},
			updateToken : function(userId) {
				return this.http.post(this.apiBase+'/?method=put&UserId='+userId+'&NewToken=NewToken');
			},
			add : function(user,userId) {
				return this.http.post(this.apiBase+'/?method=post&UserId='+userId,user);
			},
			delete : function(userId) {
				return this.http.get(this.apiBase+'/?method=delete&UserId='+userId);
			}
		}
	}
]);

app.controller('usersListController', ['$scope', '$controller', 'usersService','$location',
	function($scope, controller, usersService,$location){
		var loadAll = function(){
			usersService.getAll().success(function(data) {
					$scope.users = data.Data;
					window.document.title = "Users List";
				});
			};
		
		$scope.delete = function(user){
			usersService.delete(user.UserId).success(function(data) {
				if(data.Success) {
					alert("User deleted!");
					loadAll();
				}else{
					alert(data.Message);
				}
			});
		}
		
		loadAll();
	}
]);


app.controller('userController', ['$scope', '$controller', 'usersService','$location',
	function($scope, controller, usersService,$location){
		usersService.getById($scope.UserId).success(function(data) {
				if(data.Data==null){
					$scope.user = {};
					window.document.title = "Adding User";
				}else{
					$scope.user = data.Data;
					$scope.UserId = data.Data.UserId;
					window.document.title = "User '"+data.Data.UserId+"'";
				}
			});
		
		$scope.update = function(user){
			usersService.update(user,user.UserId).success(function(data) {
				if(data.Success) alert("User updated!");
				else{
					alert(data.Message);
					return;
				}
				$scope.user = data.Data;
				window.document.title = "User '"+data.Data.UserId+"'";
			});
		}
		
		$scope.updateToken = function(user){
			usersService.updateToken(user.UserId).success(function(data) {
				if(data.Success) alert("Token updated!");
				else{
					alert(data.Message);
					return;
				}
				$scope.user = data.Data;
				window.document.title = "User '"+data.Data.UserId+"'";
			});
		}
		
		$scope.add = function(user){
			usersService.add(user,user.UserId).success(function(data) {
				if(data.Success) alert("User added!");
				else{
					alert(data.Message);
				}
			});
		}
	}
]);
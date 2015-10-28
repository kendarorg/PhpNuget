
app.factory('profilePackagesService',['$http','pathHelper','$q',
	function($http,pathHelper,$q) {
		var doRunAllThings = function(total,skip,count){
				console.log("Running "+skip+" "+count);
				var apiBase = pathHelper.buildApiPath('packages');
				
				
				
				var prom =$http.post(apiBase+'/?method=refreshpackages&skip='+skip+'&count='+count)
					.success(function(data){
						console.log("Correct "+skip+" "+count);
						if(skip<total){
							doRunAllThings(total,skip+count,count);
						}
					}).error(function(data){
						console.log("Wrong "+skip+" "+count);
					});
			};
		return {
			apiBase : pathHelper.buildApiPath('packages'),
			http :$http,
			
			getAll : function(top,skip) {
				return this.http.get(this.apiBase+'/?Query= &top='+top+"&skip="+skip+"&DoGroup=true");
			},
			getById : function(title,version) {
				var query = encodeURIComponent("Version eq '"+version+"' and Id eq '"+title+"'");
				return this.http.get(this.apiBase+'/?Query='+query+"&DoGroup=false");
			},
			update : function(apackage,packageId,version) {
				return this.http.post(this.apiBase+'/?method=put&Id='+packageId+'&Version='+version,apackage);
			},
			download : function(url,id,version) {
				return this.http.post(this.apiBase+'/?method=download&Id='+id+'&Version='+version+'&Url='+encodeURIComponent(url));
			},
			getAllVersions : function(title) {
				var query = encodeURIComponent("Id eq '"+title+"'");
				return this.http.get(this.apiBase+'/?Query='+query);
			},
			delete : function(packageId,version) {
				return this.http.post(this.apiBase+'/?method=delete&Id='+packageId+'&Version='+version,{});
			},
			
			refreshPackages : function() {
				var skip = 0;
				var total = 0;
				var count = 30;
				var apiBase = this.apiBase;
				this.http.post(apiBase+'/?method=countpackagestorefresh')
					.success(function(data){
						total = data.Data;
						console.log("Founded items: "+total);
						doRunAllThings(total,skip,count);						
					}).error(function(data){
						console.log("Wrong count");
					});
			}
		}
	}
]);

app.controller('profilePackagesController', ['$scope', '$controller', 'profilePackagesService',
	function($scope, controller, profilePackagesService){
		$scope.hasNext = false;
		$scope.hasPrevious= false;
		$scope.next = 10
		$scope.previous= 0;
		
		profilePackagesService.getAll(11,$scope.Skip).success(function(data) {
				if(!data.Success) {
					alert(data.Message);
					return;
				}
				var skip = parseInt($scope.Skip);
				$scope.packages = data.Data;
				
				$scope.hasNext = true;
				$scope.hasPrevious = true;
				$scope.next = skip+10;
				$scope.previous = skip-10; 
				if($scope.Skip>0){
					$scope.previous = skip-10;
				}
				
				if($scope.previous<0){
					$scope.previous = 0;
					$scope.hasPrevious = false;
				}
				if($scope.packages.length<=10){
					$scope.hasNext = false;
					$scope.next = -1;
				}
				
				window.document.title = "Packages List";
			});
		
	}
]);

app.controller('profilePackageController', ['$scope', '$controller', 'profilePackagesService','$location',
	function($scope, controller, profilePackagesService,$location){
	
		profilePackagesService.getById($scope.PackageId,$scope.PackageVersion).success(function(data) {
				$scope.package = data.Data[0];
				//console.log($scope.package);
				window.document.title = "Packages List";
				profilePackagesService.getAllVersions($scope.PackageId).success(function(data) {
					$scope.versions = data.Data;
				});
			});
		
			
		$scope.update = function(apackage){
			profilePackagesService.update(apackage,apackage.Id,apackage.Version).success(function(data) {
				if(data.Success) alert("Package updated!");
				else{
					alert(data.Message);
					return;
				}
				$scope.package = data.Data;
				window.document.title = "Package '"+data.Data.Title+"'";
			});
		}
		
		$scope.delete = function(pack){
			profilePackagesService.delete(pack.Id,pack.Version).success(function(data) {
				if(data.Success) {
					alert("Package deleted!");
					$location.path('/profile/admin/packages/list/0');
				}else{
					alert(data.Message);
				}
			});
		}
	}
]);

app.controller('packagesUploadController', ['$scope', '$controller', 'profilePackagesService','$window',
	function($scope, controller, profilePackagesService,$window){
		$scope.downloadItem = {
			
		};
		$scope.downloadItem.Url ="https://www.nuget.org/api/v2/package/@ID/@VERSION";
		
		window.packagesUploadControllerCallback = function(result,packageId,packageVersion,message){
			if(result){
				$window.location.hash = "/profile/"+$scope.UserId+"/packages/"+packageId+"/"+packageVersion;
			}else{
				alert("Error uploading file:\r\n"+message);
			}
		}
		
		$scope.download = function(url,packageId,packageVersion){
			profilePackagesService.download($scope.downloadItem.Url,$scope.downloadItem.Id,$scope.downloadItem.Version).success(function(data) {
					if(data.Success) alert("Package downloaded!");
					else{
						alert(data.Message);
						return;
					}
				});
		}
		
		$scope.refreshPackages = function(){
			profilePackagesService.refreshPackages();
		}
	}
]);
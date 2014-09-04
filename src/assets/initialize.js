
app.config(["pathHelperProvider", function(pathHelperProvider) {
	var pathName = window.location.pathname.rtrim("/").ltrim("/");
	var pathNameApi = pathName+"/api";
	
	pathHelperProvider.setupApiPath(pathNameApi);
	pathHelperProvider.setupRootPath(pathName);
}]);
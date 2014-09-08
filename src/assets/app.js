
var app = angular.module('phpNugetApp', 
	['ui.router']
);

app.run(function($rootScope) {
    $rootScope.cleanUpUrl = function(url)
	{
		var realUrl = "";
		var hashPos = url.indexOf('#');
		if(hashPos>0){
			realUrl = url.substring(hashPos+1);
		}
		return url.substring(0,url.indexOf("?"))+realUrl;
	};
})



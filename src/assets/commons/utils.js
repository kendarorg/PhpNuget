String.prototype.rtrim = function(s) { 
    return this.replace(new RegExp(s + "*$"),''); 
};
String.prototype.ltrim = function(s) { 
    return this.replace(new RegExp("^"+ s + "*"),''); 
};

app.provider("pathHelper",[function(){
	var rootPath = null;
	var apiPath = null;
	
	this.setupApiPath = function(apiPath){
		this.apiPath ="/"+apiPath.rtrim("/").ltrim("/");
	}
	this.buildApiPath = function(path){
		return this.apiPath+"/"+path.ltrim("/");
	}
	
	this.setupRootPath = function(rootPath){
		this.rootPath ="/"+rootPath.rtrim("/").ltrim("/");
	}
	this.buildPath = function(path){
		//return this.rootPath+"/"+path.ltrim("/");
		var t= this.rootPath+"/"+path.ltrim("/");
		return "/"+t.ltrim("/");
	}
	this.buildViewsPath = function(path){
		return this.buildAppPath("views/"+path.ltrim("/"));
	}
	this.buildAppPath = function(path){
		return this.buildPath("assets/"+path.ltrim("/"));
	}
	
	this.$get = [function helperInstanceFactory(){
		return this;
	}];
}]);
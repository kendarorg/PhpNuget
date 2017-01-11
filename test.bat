@echo off
SET APIKEY=B276AFA8-D51E-1FCC-CE5F-85434EC4392B
SET NUROOT=http://localhost:8080/edsa-nuget

cd nuget_exes

echo NuGet.2.8, Upload of simple file
del /Q ..\src\data\db\nugetdb_pkg.txt
del /Q ..\src\data\packages\*.nupkg

NuGet.2.8.exe SetApiKey %APIKEY% -Source %NUROOT%/upload 
NuGet.2.8.exe Push Dapper.1.42.nupkg -Source %NUROOT%/upload
dir ..\src\data\packages\*.nupkg

echo NuGet.3.4, Upload of simple file
del /Q ..\src\data\db\nugetdb_pkg.txt
del /Q ..\src\data\packages\*.nupkg

NuGet.3.4.exe SetApiKey %APIKEY% -Source %NUROOT%/upload 
NuGet.3.4.exe Push Dapper.1.42.nupkg -Source %NUROOT%/upload
dir ..\src\data\packages\*.nupkg

echo NuGet.3.5, Upload of simple file
del /Q ..\src\data\db\nugetdb_pkg.txt
del /Q ..\src\data\packages\*.nupkg

NuGet.3.5.exe SetApiKey %APIKEY% -Source %NUROOT%/upload 
NuGet.3.5.exe Push Dapper.1.42.nupkg -Source %NUROOT%/upload
dir ..\src\data\packages\*.nupkg

cd ..
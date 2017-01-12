@echo off
SET APIKEY=E7446C12-BD80-4272-3332-09914BE6EBC8
SET NUROOT=http://localhost:8080/edsa-nuget

cd nuget_exes

echo NuGet.2.8, Upload of simple file

NuGet.2.8.exe SetApiKey %APIKEY% -Source %NUROOT%/upload 
NuGet.2.8.exe Push Moq.4.5.28.nupkg -Source %NUROOT%/upload
dir ..\src\data\packages\*.nupkg

echo NuGet.3.4, Upload of simple file

NuGet.3.4.exe SetApiKey %APIKEY% -Source %NUROOT%/upload 
NuGet.3.4.exe Push Newtonsoft.Json.4.5.11.nupkg -Source %NUROOT%/upload
dir ..\src\data\packages\*.nupkg


echo NuGet.3.5, Upload of simple file

NuGet.3.5.exe SetApiKey %APIKEY% -Source %NUROOT%/upload 
NuGet.3.5.exe Push NLog.4.3.0.nupkg -Source %NUROOT%/upload
dir ..\src\data\packages\*.nupkg

cd ..
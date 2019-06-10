@echo off
SET APIKEY=C4FF6043-C8D9-0F3C-982C-100FD3451FBE
SET NUROOT=http://localhost:9999

cd nuget_exes


echo NuGet.2.8, Upload of simple file

NuGet.2.8.exe SetApiKey %APIKEY% -Source %NUROOT%/upload 
NuGet.2.8.exe Push Moq.4.5.28.nupkg -Source %NUROOT%/upload
dir ..\src\data\packages\*.nupkg

echo NuGet.3.4, Upload of simple file

NuGet.3.4.exe SetApiKey %APIKEY% -Source %NUROOT%/upload 
NuGet.3.4.exe Push Newtonsoft.Json.4.5.11.nupkg -Source %NUROOT%/upload
dir ..\src\data\packages\*.nupkg


echo NuGet.5.0, Upload of simple file

NuGet.5.0.exe SetApiKey %APIKEY% -Source %NUROOT%/upload 
NuGet.5.0.exe Push NLog.4.3.0.nupkg -Source %NUROOT%/upload
dir ..\src\data\packages\*.nupkg

echo NuGet.5.0, Upload of simple file

NuGet.5.0.exe SetApiKey %APIKEY% -Source %NUROOT%/upload 
NuGet.5.0.exe Push NoRelease.1.0.0.nupkg -Source %NUROOT%/upload
dir ..\src\data\packages\*.nupkg


echo NuGet.5.0, Upload of simple file

NuGet.5.0.exe SetApiKey %APIKEY% -Source %NUROOT%/upload 
NuGet.5.0.exe Push Hebrew.1.0.0.nupkg -Source %NUROOT%/upload
dir ..\src\data\packages\*.nupkg

cd ..
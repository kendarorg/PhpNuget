@echo off
SET APIKEY=C4FF6043-C8D9-0F3C-982C-100FD3451FBE
SET NUROOT=http://localhost:9999

cd nuget_exes

echo NuGet.5.0, Common libs

SET PCKG=EPG.CommonLibrary.1.1.21.7096.symbols.nupkg
NuGet.5.0.exe SetApiKey %APIKEY% -Source %NUROOT%/symbolupload 
NuGet.5.0.exe Push %PCKG% -Source %NUROOT%/symbolupload
dir ..\src\data\packages\EPG.CommonLibrary.1.1.21.7096.snupkg > NULL 2>&1
if errorlevel 1 (
   GOTO FINISH
)

echo NuGet.5.0, Upload of Symbols file

SET PCKG=newtonsoft.json.12.0.2.symbols.nupkg
NuGet.5.0.exe SetApiKey %APIKEY% -Source %NUROOT%/symbolupload
NuGet.5.0.exe Push %PCKG% -Source %NUROOT%/symbolupload
dir ..\src\data\packages\newtonsoft.json.12.0.2.snupkg> NULL 2>&1
if errorlevel 1 (
   GOTO FINISH
)

SET PCKG=Newtonsoft.Json.12.0.2.nupkg
NuGet.5.0.exe SetApiKey %APIKEY% -Source %NUROOT%/upload
NuGet.5.0.exe Push %PCKG% -Source %NUROOT%/upload
dir ..\src\data\packages\%PCKG% > NULL 2>&1
if errorlevel 1 (
   GOTO FINISH
)

echo NuGet.2.8, Upload of simple file

SET PCKG=Moq.4.5.28.nupkg
NuGet.2.8.exe SetApiKey %APIKEY% -Source %NUROOT%/upload 
NuGet.2.8.exe Push %PCKG% -Source %NUROOT%/upload
dir ..\src\data\packages\%PCKG% > NULL 2>&1
if errorlevel 1 (
   GOTO FINISH
)

echo NuGet.3.4, Upload of simple file

SET PCKG=NLog.4.3.0.nupkg
NuGet.3.4.exe SetApiKey %APIKEY% -Source %NUROOT%/upload 
NuGet.3.4.exe Push %PCKG% -Source %NUROOT%/upload
dir ..\src\data\packages\%PCKG% > NULL 2>&1
if errorlevel 1 (
   GOTO FINISH
)

echo NuGet.5.0, Upload of simple file

SET PCKG=NoRelease.1.0.0.nupkg
NuGet.5.0.exe SetApiKey %APIKEY% -Source %NUROOT%/upload 
NuGet.5.0.exe Push %PCKG% -Source %NUROOT%/upload
dir ..\src\data\packages\%PCKG% > NULL 2>&1
if errorlevel 1 (
   GOTO FINISH
)


echo NuGet.5.0, Upload of simple file

SET PCKG=Hebrew.1.0.0.nupkg
NuGet.5.0.exe SetApiKey %APIKEY% -Source %NUROOT%/upload 
NuGet.5.0.exe Push %PCKG% -Source %NUROOT%/upload
dir ..\src\data\packages\%PCKG% > NULL 2>&1
if errorlevel 1 (
   GOTO FINISH
)



:FINISH
del null
cd ..

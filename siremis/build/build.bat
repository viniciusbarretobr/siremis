@echo off

rem *********************************************************************
rem cubi builder script. Usage: build app_name
rem *********************************************************************

set PHING_HOME=..\bin\phing

..\bin\phing\bin\phing -buildfile %1.xml %2


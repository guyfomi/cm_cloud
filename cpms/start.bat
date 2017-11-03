@echo off

set _ODDJOB_HOME=C:\xampp\htdocs\innovics\cpms\oddjob-1.4.0\oddjob

set _ODDJOB_JAR="%_ODDJOB_HOME%\run-oddjob.jar"
set _ODDJOB_XML="%_ODDJOB_HOME%\innovics_job.xml"

if not exist %_ODDJOB_JAR% goto failNoOddjob

if "%JAVA_HOME%" == "" goto noJavaHome
set _JAVA_CMD=%JAVA_HOME%/bin/java.exe
goto haveJavaCmd

:noJavaHome
set _JAVA_CMD=java

:haveJavaCmd

goto runOddjob

:failNoOddjob
echo "run-oddjob.jar can not be found at %_ODDJOB_JAR%."

fail:
exit /b 1

:runOddjob

"%_JAVA_CMD%" -jar %_ODDJOB_JAR% -f %_ODDJOB_XML%
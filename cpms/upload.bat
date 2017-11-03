@echo off

"C:\xampp\htdocs\innovics\cpms\WinSCP-5.9.4-Portable\WinSCP.com" ^
  /log="C:\temp\WinSCP.log" /ini=nul ^
  /command ^
    "open -certificate="*" ftpes://innovics%%40innovics.centre-albert-einstein.com:Guy2p%%40cc@ftp.centre-albert-einstein.com/" ^
    "cd download" ^
    "cd parameters" ^
    "lcd C:\xampp\htdocs\innovics\download\stage" ^
    "put *.csv" ^
    "exit"

set WINSCP_RESULT=%ERRORLEVEL%
if %WINSCP_RESULT% equ 0 (
  echo Success
) else (
  echo Error
)

exit /b %WINSCP_RESULT%

@echo off
echo ========================================
echo    TikTok Webhook Tunnel Setup
echo ========================================
echo.
echo 1. Make sure Laravel server is running on port 8000
echo 2. This will create a public URL for webhook
echo 3. Copy the URL and update in TikTok Partner Portal
echo.
echo Starting LocalTunnel...
echo.

:retry
lt --port 8000 --subdomain hmtik-webhook-2025
if %errorlevel% neq 0 (
    echo.
    echo Tunnel failed, retrying in 5 seconds...
    timeout /t 5 /nobreak > nul
    goto retry
)

pause

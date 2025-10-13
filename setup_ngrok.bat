@echo off
echo ========================================
echo    TikTok Webhook Setup with Ngrok
echo ========================================
echo.

echo 1. Starting Laravel development server...
start "Laravel Server" cmd /k "php artisan serve --host=0.0.0.0 --port=8000"

echo.
echo 2. Waiting 3 seconds for Laravel to start...
timeout /t 3 /nobreak > nul

echo.
echo 3. Starting Ngrok tunnel...
echo    This will create a public URL for your localhost:8000
echo    Copy the HTTPS URL and use it in TikTok Partner Portal
echo.

ngrok http 8000

echo.
echo ========================================
echo Setup complete! 
echo.
echo Next steps:
echo 1. Copy the HTTPS URL from ngrok (e.g., https://abc123.ngrok.io)
echo 2. Go to TikTok Partner Portal
echo 3. Set webhook URL to: https://abc123.ngrok.io/tiktok/webhook/handle
echo 4. Test webhook at: https://abc123.ngrok.io/tiktok/webhook/test
echo ========================================
pause



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Maintenance - TikTool</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #fff;
        }

        .maintenance-container {
            max-width: 600px;
            width: 100%;
            text-align: center;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 60px 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .maintenance-icon {
            font-size: 80px;
            margin-bottom: 30px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.1);
                opacity: 0.8;
            }
        }

        h1 {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 20px;
            color: #fff;
        }

        .subtitle {
            font-size: 18px;
            margin-bottom: 30px;
            color: rgba(255, 255, 255, 0.9);
            line-height: 1.6;
        }

        .message {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            border-left: 4px solid #fff;
        }

        .message p {
            font-size: 16px;
            line-height: 1.8;
            color: rgba(255, 255, 255, 0.95);
        }

        .info-box {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            text-align: left;
        }

        .info-box h3 {
            font-size: 18px;
            margin-bottom: 15px;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-box ul {
            list-style: none;
            padding-left: 0;
        }

        .info-box li {
            padding: 8px 0;
            padding-left: 30px;
            position: relative;
            color: rgba(255, 255, 255, 0.9);
        }

        .info-box li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #4ade80;
            font-weight: bold;
        }

        .refresh-button {
            display: inline-block;
            background: #fff;
            color: #667eea;
            padding: 15px 40px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .refresh-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
            background: #f0f0f0;
        }

        .countdown {
            margin-top: 20px;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.8);
        }

        .loading-dots {
            display: inline-block;
        }

        .loading-dots span {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #fff;
            margin: 0 3px;
            animation: loading 1.4s infinite ease-in-out both;
        }

        .loading-dots span:nth-child(1) {
            animation-delay: -0.32s;
        }

        .loading-dots span:nth-child(2) {
            animation-delay: -0.16s;
        }

        @keyframes loading {
            0%, 80%, 100% {
                transform: scale(0);
                opacity: 0.5;
            }
            40% {
                transform: scale(1);
                opacity: 1;
            }
        }

        @media (max-width: 640px) {
            .maintenance-container {
                padding: 40px 20px;
            }

            h1 {
                font-size: 28px;
            }

            .maintenance-icon {
                font-size: 60px;
            }
        }
    </style>
</head>
<body>
    <div class="maintenance-container">
        <div class="maintenance-icon">
            🔧
        </div>
        
        <h1>We are under maintenance</h1>
        
        <p class="subtitle">
            We are upgrading and maintaining the system to bring you a better experience.
        </p>

        <div class="message">
            <p>
                <strong>Sorry for the inconvenience!</strong><br>
                The system will be back online as soon as possible. Please check back later.
            </p>
        </div>

        <div class="info-box">
            <h3>
                <span>📋</span>
                Maintenance Information
            </h3>
            <ul>
                <li>Updating and improving the system</li>
                <li>Your data remains safe</li>
                <li>The system will be back as soon as possible</li>
                <li>All services will be restored after completion</li>
            </ul>
        </div>

        <a href="javascript:location.reload()" class="refresh-button">
            <span>🔄</span> Reload page
        </a>

        <div class="countdown">
            <div class="loading-dots">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <div style="margin-top: 10px;">Checking system status...</div>
        </div>
    </div>

    <script>
        // Auto refresh every 30 seconds
        let refreshInterval = setInterval(function() {
            fetch(window.location.href, { 
                method: 'HEAD',
                cache: 'no-cache'
            })
            .then(response => {
                if (response.status !== 503) {
                    // System is back online, reload page
                    clearInterval(refreshInterval);
                    window.location.reload();
                }
            })
            .catch(error => {
                console.log('Still in maintenance mode');
            });
        }, 30000);

        // Manual refresh button
        document.querySelector('.refresh-button').addEventListener('click', function(e) {
            e.preventDefault();
            window.location.reload();
        });
    </script>
</body>
</html>



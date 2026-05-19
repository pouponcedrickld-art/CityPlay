<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre code de vérification</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f4f7f9;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .header {
            background-color: #1a1a1a;
            color: #ffffff;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        .content {
            padding: 40px;
            text-align: center;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
        }
        .otp-container {
            background-color: #f8fafc;
            border: 2px dashed #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
        }
        .otp-code {
            font-size: 42px;
            font-weight: 800;
            color: #FF9500;
            letter-spacing: 8px;
            margin: 0;
        }
        .footer {
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            background-color: #f8fafc;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #FF9500;
            color: #ffffff;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>CityQuest</h1>
        </div>
        <div class="content">
            <div class="greeting">Bonjour {{ $userName }},</div>
            <p>Voici votre code de vérification à usage unique (OTP) pour accéder à votre compte :</p>
            
            <div class="otp-container">
                <div class="otp-code">{{ $otp }}</div>
            </div>
            
            <p>Ce code est valide pour une durée limitée. Si vous n'avez pas demandé ce code, veuillez ignorer cet email.</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} CityQuest - Explorez votre ville autrement.
        </div>
    </div>
</body>
</html>
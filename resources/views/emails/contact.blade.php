<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nouveau message de contact</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
        }
        .container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background-color: #f8b800;
            color: #1f2937;
            padding: 15px;
            text-align: center;
            font-weight: bold;
        }
        .content {
            padding: 20px;
        }
        .field {
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .field:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: bold;
            color: #666;
            margin-bottom: 5px;
        }
        .value {
            padding: 5px 0;
        }
        .footer {
            margin-top: 20px;
            padding: 10px;
            font-size: 12px;
            color: #666;
            text-align: center;
            background-color: #f9f9f9;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Nouveau message de contact</h2>
        </div>
        
        <div class="content">
            <div class="field">
                <div class="label">Nom:</div>
                <div class="value">{{ $data['name'] }}</div>
            </div>
            
            <div class="field">
                <div class="label">Email:</div>
                <div class="value">{{ $data['email'] }}</div>
            </div>
            
            <div class="field">
                <div class="label">Sujet:</div>
                <div class="value">{{ $data['subject'] }}</div>
            </div>
            
            <div class="field">
                <div class="label">Message:</div>
                <div class="value">{{ $data['message'] }}</div>
            </div>
        </div>
        
        <div class="footer">
            <p>Ce message a été envoyé via le formulaire de contact du site Permi-Conduit.</p>
        </div>
    </div>
</body>
</html> 
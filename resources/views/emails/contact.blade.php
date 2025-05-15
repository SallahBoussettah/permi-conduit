<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>New Contact Form Submission</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #1f2937;
            color: #fff;
            padding: 15px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            padding: 20px;
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 5px 5px;
        }
        .field {
            margin-bottom: 15px;
        }
        .label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }
        .value {
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 3px;
        }
        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #666;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>New Contact Form Submission</h1>
    </div>
    
    <div class="content">
        <div class="field">
            <div class="label">Name:</div>
            <div class="value">{{ $data['name'] }}</div>
        </div>
        
        <div class="field">
            <div class="label">Email:</div>
            <div class="value">{{ $data['email'] }}</div>
        </div>
        
        <div class="field">
            <div class="label">Subject:</div>
            <div class="value">{{ $data['subject'] }}</div>
        </div>
        
        <div class="field">
            <div class="label">Message:</div>
            <div class="value">{{ $data['message'] }}</div>
        </div>
    </div>
    
    <div class="footer">
        <p>This email was sent from the contact form on the ECF website.</p>
    </div>
</body>
</html> 
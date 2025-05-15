<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('app.contact_confirmation_subject') }}</title>
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
        .message {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #666;
            text-align: center;
        }
        .button {
            display: inline-block;
            background-color: #f59e0b;
            color: #1f2937;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            margin-top: 15px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ __('app.contact_confirmation_subject') }}</h1>
    </div>
    
    <div class="content">
        @if(App::isLocale('fr'))
            <p>Cher(e) {{ $data['name'] }},</p>
            
            <p>Merci de nous avoir contacté. Nous avons bien reçu votre message et nous vous répondrons dans les plus brefs délais.</p>
            
            <div class="message">
                <p><strong>Sujet:</strong> {{ $data['subject'] }}</p>
                <p><strong>Votre message:</strong></p>
                <p>{{ $data['message'] }}</p>
            </div>
            
            <p>Si vous avez des questions supplémentaires ou des informations à nous fournir, n'hésitez pas à répondre à cet e-mail.</p>
            
            <p>
                Cordialement,<br>
                L'équipe ECF
            </p>
            
            <a href="{{ url('/') }}" class="button">Visiter notre site web</a>
        @else
            <p>Dear {{ $data['name'] }},</p>
            
            <p>Thank you for reaching out to us. We have received your message and will get back to you as soon as possible.</p>
            
            <div class="message">
                <p><strong>Subject:</strong> {{ $data['subject'] }}</p>
                <p><strong>Your message:</strong></p>
                <p>{{ $data['message'] }}</p>
            </div>
            
            <p>If you have any additional questions or information to provide, please don't hesitate to reply to this email.</p>
            
            <p>
                Best regards,<br>
                The ECF Team
            </p>
            
            <a href="{{ url('/') }}" class="button">Visit Our Website</a>
        @endif
    </div>
    
    <div class="footer">
        @if(App::isLocale('fr'))
            <p>Ceci est une réponse automatique. Veuillez ne pas répondre directement à cet e-mail.</p>
            <p>&copy; {{ date('Y') }} ECF. Tous droits réservés.</p>
        @else
            <p>This is an automated response. Please do not reply directly to this email.</p>
            <p>&copy; {{ date('Y') }} ECF. All rights reserved.</p>
        @endif
    </div>
</body>
</html> 
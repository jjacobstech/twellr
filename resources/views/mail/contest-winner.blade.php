
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <meta name="Design Fest">
      <meta name="Twellr">
    <title>Congratulations on Winning DesignFest!</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background-color: #001f54;
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }

        .content {
            background-color: #ffffff;
            padding: 30px;
            border-left: 1px solid #eeeeee;
            border-right: 1px solid #eeeeee;
        }

        h1 {
            color: #ffffff;
            margin: 0;
            font-size: 28px;
        }

        h2 {
            color: #001f54;
            margin-top: 30px;
            font-size: 20px;
            border-bottom: 1px solid #eeeeee;
            padding-bottom: 10px;
        }

        ul {
            padding-left: 20px;
        }

        .footer {
            background-color: #f5f5f5;
            padding: 20px;
            border-radius: 0 0 5px 5px;
            font-size: 14px;
            color: #666666;
            text-align: center;
            border: 1px solid #eeeeee;
        }

        .button {
            background-color: #001f54;
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 4px;
            display: inline-block;
            margin: 20px 0;
            font-weight: bold;
        }
        .button:hover {
            background-color: #fbaa0d;
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 4px;
            display: inline-block;
            margin: 20px 0;
            font-weight: bold;
        }

        .highlight {
            font-weight: bold;
            color: #001f54;
        }
        .highlight:hover {
            font-weight: bold;
            color: #fbaa0d;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Congratulations! You've Won {{ $contest }}!</h1>
    </div>
    <div class="content">
        <p>Dear {{ $user }},</p>

        <p><strong>Great news!</strong> We're thrilled to inform you that your submission has been selected as the <span
                class="highlight">winner of {{ $contest }}</span>!</p>

        <h2>Your Prize</h2>

        <p>As the winner of {{ $contest }}, you've earned:</p>

        <ul>
            <li>A discount of [{{ config('twellr.discount') }}]% on your account</li>
        </ul>

        <h2>Next Steps</h2>

        <p>Your discount has been automatically applied to your account and is ready to use on your next purchase. To
            view your updated account details, simply log in to your profile.</p>


        <a href="https://www.twellr.com/login" class="button">View Your Account</a>

        <p>Congratulations once again on this well-deserved recognition. We look forward to seeing more of your creative
            work in the future!</p>

        <p>
            Best regards,<br>
            The Twellr Team
        </p>
    </div>

    <div class="footer">
        If you have any questions about your prize or need assistance, please contact our support team on Twellr support page
    </div>
</body>

</html>

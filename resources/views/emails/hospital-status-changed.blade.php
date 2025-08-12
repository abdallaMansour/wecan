<!DOCTYPE html>
<html lang="en">

<head>
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #f2e4cf;
        }

        .message {
            background-color: #f4ebdf;
            padding: 20px;
            border-radius: 10px;
            margin: 20px;
            font-size: 16px;
            line-height: 1.5;
            color: #333;
            text-align: center;
            max-width: 600px;
            margin: 0 auto;
            box-shadow: 0 0 10px #e8b86a;
        }

        .message h1 {
            color: #F3A524;
            font-size: 24px;
            margin-bottom: 10px;
        }

        .message a {
            color: #F3A524;
            text-decoration: none;
        }

        .message a:hover {
            text-decoration: underline;
        }

        .message p {
            margin-bottom: 10px;
        }

        .message p:last-child {
            margin-bottom: 0;
        }

        .message p:first-child {
            margin-top: 0;
        }
    </style>
</head>

<body>


    <div class="message">
        <h1>Hospital Status Changed</h1>
        <p>The status of your hospital has been changed to {{ $hospital->account_status }}.</p>
        <p>You can login to your dashboard <a href="{{ env('HOSPITAL_URL') }}/login?hospital={{ $hospital->id }}">here</a></p>
        <p>Thank you for using our platform.</p>
        <p>Best regards,</p>
        <p>The {{ config('app.name') }} Team</p>
    </div>

</body>

</html>

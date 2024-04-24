<!--<!DOCTYPE html>-->
<!--<html lang="en">-->
<!--<head>-->
<!--    <meta charset="UTF-8">-->
<!--    <meta name="viewport" content="width=device-width, initial-scale=1.0">-->
<!--    <meta http-equiv="X-UA-Compatible" content="ie=edge">-->
<!--    <title>Document</title>-->
<!--</head>-->
<!--<body>-->
<!--    <h3>-->
<!--        {{$details['WebsiteName']}} -->
<!--    </h3>-->
<!--    <p>-->
<!--        {{$details['content']}}-->
<!--    </p>-->
<!--</body>-->
<!--</html>-->

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Device Verification</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
        }

        .email-template {
            width: 600px;
            margin: auto;
            align-items: center;
        }

        .email-template_logo {
            width: 150px;
            padding: 30px 0;
        }

        .email-template table {
            background-color: #080f14;
            width: 100%;
            border: 0;
        }

        .email-template td {
            padding: 20px 60px;
        }

        .email-template h1 {
            font-size: 28px;
            font-weight: 700;
            margin: 0;
            color: #ff3836;
        }

        .email-body {
            color: #fff;
            font-size: 17px;
        }

        .email-btn {
            font-size: 16px;
            background: #ff3836;
            color: #fff;
            padding: 13px 16px;
            border: 0;
            outline: 0;
            box-shadow: unset;
            cursor: pointer;
            font-weight: 700;
        }

        .red-text {
            color: #ff3836;
        }
    </style>
</head>

<body>
    <div class="email-template">

        <table>

            <tr>
                <th>
                    <img alt="Logo" src="{{ asset('images/logo.png') }}" class="email-template_logo" />
                    <h1>Dear {{$user['name']}}</h1>
                </th>
            </tr>

            <tr>
                <td>
                    <p class="email-body">
                        {{ $details['content'] }}
                    </p>

                    <p class="email-body red-text">Team Company Support !</p>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>

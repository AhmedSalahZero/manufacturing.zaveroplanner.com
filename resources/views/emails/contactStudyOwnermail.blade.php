<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-GB">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Zavero</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <style type="text/css">

        a[x-apple-data-detectors] {
            color: inherit !important;
        }

    </style>

</head>

<body style="margin: 0; padding: 0;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td style="padding: 20px 0 30px 0;">

                <table align="center" border="0" cellpadding="0" cellspacing="0" width="600"
                    style="border-collapse: collapse; border: 1px solid #cccccc;">
                    <tr>
                        <td align="center" bgcolor="#70bbd9"
                        style="padding: 345px 0 30px 0; background: url({{url('assets/images/back_2.png')}}) ; background-size: cover;">
                        </td>
                    </tr>
                    <tr>
                        <td bgcolor="#ffffff" style="padding: 40px 30px 40px 30px;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%"
                                style="border-collapse: collapse;">
                                <tr>
                                    <td style="color: #153643; ">
                                        <h4 style="font-size: 18px; margin: 0; display:block;padding-bottom: 9px;">From : {{$details['sender_name']}}</h4>
                                        <h4 style="font-size: 18px; margin: 0; display:block;padding-bottom: 9px;">E-mail : {{$details['sent_from']}}</h4>
                                    </td>
                                </tr>

                                    <tr>
                                        <td width="auto" bgcolor="#ffffff"  style="text-align: left" >
                                            <div style="padding: 5px;background: #ffffff;
                                            display: block;
                                            width: auto;
                                            margin: 0 auto;
                                            padding: 0;
                                            position: relative;
                                            width: 350px;
                                            max-width: 90%;margin-top: 2%;
                                            ">
                                            @if ($details['send_to'] == "zavero_team")
                                                <h3>Study-Owner : {{$details['owner_name']}}</h3>
                                                <p style="margin: 0;color: #153643;  font-size: 16px; line-height: 20px;">{!! $details['message'] !!}</p>
                                            @else
                                                <h3>Hello {{$details['owner_name']}}</h3>
                                                <p style="margin: 0;color: #153643;  font-size: 16px; line-height: 20px;">{!! $details['message'] !!}</p>
                                            @endif

                                            </div>
                                        </td>


                                    </tr>

                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td bgcolor="#ee4c50" style="padding: 30px 30px;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%"
                                style="border-collapse: collapse;">
                                <tr>
                                    <td style="color: #ffffff;  font-size: 14px;">
                                        <p style="margin: 0;">&reg; ZAVERO Magic Sheet<br />

                                        </p>
                                    </td>

                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>
</body>

</html>

<!DOCTYPE html>
<html>
<head>
    <title>Revero User Comments</title>
</head>
<body>
    <h3>Mail From : {{ $details['user_info']['email'] }} </h3>
    <h3>Name : {{ $details['user_info']['name'] .' '. $details['user_info']['last_name'] }} </h3>
    <h3>Company Name : {{ $details['user_info']['company_name'] }} </h3>
    <h3>Department : {{ $details['user_info']['department'] }} </h3>
    <h3>Subject : {{ $details['subject'] }}</h3>
    <p><h3>Message : </h3> {!! $details['message'] !!}</p>

</body>
</html>

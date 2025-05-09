<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Poll beëindigd</title>
</head>
<body>
    <h2>De poll "{{ $poll->title }}" is beëindigd</h2>

    <p>
        De stemming is afgerond en de best gekozen datum is:
        <strong>{{ \Carbon\Carbon::parse($finalDate)->format('d-m-Y') }}</strong>.
    </p>

    <p>Bedankt voor je deelname!</p>

    <hr>
    <small>Deze e-mail werd automatisch verzonden na afloop van de poll.</small>
</body>
</html>

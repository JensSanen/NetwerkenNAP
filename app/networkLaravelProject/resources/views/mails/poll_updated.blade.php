<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Nieuwe stem op poll</title>
</head>
<body>
    <h2>Er is een nieuwe stem uitgebracht!</h2>

    <p>
        De deelnemer <strong>{{ $participant->email }}</strong> heeft zojuist gestemd op de poll:
        <strong>{{ $poll->title }}</strong>.
    </p>

    <p>Je kunt de poll bekijken via de gebruikelijke link.</p>

    <hr>
    <small>Dit is een automatische melding van je stemronde.</small>
</body>
</html>

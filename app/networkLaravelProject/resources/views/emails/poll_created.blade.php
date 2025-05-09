<!DOCTYPE html>
<html>
<head>
    <title>Bevestiging poll</title>
</head>
<body>
    <h1>Bedankt voor het aanmaken van je poll!</h1>
    <p>Titel: {{ $poll->title }}</p>
    <p>Beschrijving: {{ $poll->description }}</p>
    <p>Locatie: {{ $poll->location }}</p>

    <p>Je kunt je poll bekijken via: <a href="{{ url('/poll/' . $poll->id) }}">{{ url('/poll/' . $poll->id) }}</a></p>
</body>
</html>

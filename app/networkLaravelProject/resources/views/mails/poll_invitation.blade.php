<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Uitnodiging om te stemmen</title>
</head>
<body>
    <h2>Hallo,</h2>
    <p>Je bent uitgenodigd om te stemmen op een datum voor een evenement.</p>

    <p><strong>Poll titel:</strong> {{ $participant->poll->title }}</p>
    <p><strong>Locatie:</strong> {{ $participant->poll->location }}</p>
    <p><strong>Beschrijving:</strong> {{ $participant->poll->description }}</p>

    <p>Klik op onderstaande knop om je voorkeuren aan te geven:</p>

    <p>
        <a href="{{ $voteUrl }}" style="padding: 10px 20px; background-color: #28a745; color: white; text-decoration: none; border-radius: 4px;">
            Stem nu
        </a>
    </p>

    <p>Bedankt!</p>
</body>
</html>

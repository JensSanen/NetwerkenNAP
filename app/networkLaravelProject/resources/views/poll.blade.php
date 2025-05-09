<!DOCTYPE html>
<html>
<head>
    <title>Poll Info</title>
</head>
<body>
    <h1>{{ $poll->title }}</h1>
    <p><strong>Beschrijving:</strong> {{ $poll->description }}</p>
    <p><strong>Locatie:</strong> {{ $poll->location }}</p>

    <h2>Beschikbare datums</h2>
    <ul>
        @foreach($poll->pollDates as $date)
            <li>{{ $date->date }}</li>
        @endforeach
    </ul>

    <h2>Deelnemers</h2>
    <ul>
        @foreach($poll->participants as $participant)
            <li>
                {{ $participant->email }} –
                <a href="{{ url('/poll/' . $poll->id . '/vote/' . $participant->id) }}">
                    Unieke stemlink
                </a>
            </li>
        @endforeach
    </ul>
</body>
</html>

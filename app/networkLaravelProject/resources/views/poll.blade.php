<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Stem op poll</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h1 class="card-title">{{ $poll->title }}</h1>
            <p><strong>Beschrijving:</strong> {{ $poll->description }}</p>
            <p><strong>Locatie:</strong> {{ $poll->location }}</p>
        </div>
    </div>

    <form onsubmit="vote(event)">
        <input type="hidden" id="poll_id" value="{{ $poll->id }}">
        <input type="hidden" id="participant_id" value="{{ $participant->id }}">

        @foreach($poll->pollDates as $date)
            <div class="form-check">
                <input class="form-check-input date-checkbox" type="checkbox" value="{{ $date->id }}" id="date{{ $date->id }}">
                <label class="form-check-label" for="date{{ $date->id }}">
                    {{ \Carbon\Carbon::parse($date->date)->format('d-m-Y') }}
                </label>
            </div>
        @endforeach

        <button type="submit" class="btn btn-primary mt-3">Stem opslaan</button>
    </form>
</div>

<script>
    async function vote(event) {
        event.preventDefault();

        const poll_id = document.getElementById('poll_id').value;
        const participant_id = document.getElementById('participant_id').value;
        const dates = Array.from(document.querySelectorAll('.date-checkbox'))
                          .filter(cb => cb.checked)
                          .map(cb => parseInt(cb.value));

        const response = await fetch('/api/vote', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                poll_id: poll_id,
                participant_id: participant_id,
                dates: dates
            })
        });

        const result = await response.json();

        if (response.ok) {
            alert('Stem succesvol opgeslagen!');
            location.reload();
        } else {
            alert('Fout bij opslaan: ' + (result.message || 'Onbekende fout'));
            console.error(result);
        }
    }
</script>

</body>
</html>

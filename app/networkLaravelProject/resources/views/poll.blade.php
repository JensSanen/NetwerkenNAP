<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Stem op poll</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
<body class="bg-light">

<div class="container my-5">

    <!-- Modal: Deelnemer uitnodigen -->
    <div class="modal fade" id="inviteModal" tabindex="-1" aria-labelledby="inviteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="inviteForm" class="modal-content" onsubmit="submitInvite(event)">
        <div class="modal-header">
            <h5 class="modal-title" id="inviteModalLabel">Deelnemer uitnodigen</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Sluiten"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="invite_poll_id">
            <div class="mb-3">
            <label for="emails" class="form-label">E-mailadressen (gescheiden door komma's)</label>
            <input type="text" name="emails" class="form-control" id="invite_emails" required>
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Uitnodigen</button>
        </div>
        </form>
    </div>
    </div>

    <!-- Modal: Datum toevoegen -->
    <div class="modal fade" id="dateModal" tabindex="-1" aria-labelledby="dateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="dateForm" class="modal-content" onsubmit="submitDate(event)">
        <div class="modal-header">
            <h5 class="modal-title" id="dateModalLabel">Datum toevoegen</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Sluiten"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="date_poll_id">
            <div class="mb-3">
            <label for="new_date" class="form-label">Datum (DD-MM-YYYY)</label>
            <input type="text" class="form-control" id="new_date" required>
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-secondary">Toevoegen</button>
        </div>
        </form>
    </div>
    </div>

    @if($viewOnly)
        <div class="alert alert-info">
            Je kunt deze poll alleen bekijken. Je kunt geen stemmen uitbrengen.
        </div>
    @endif

    @if($isCreator && !$viewOnly)
        <div class="card mb-4">
            <div class="card-body">
                <h5>🎉 Je bent de maker van deze poll</h5>
                <a href="javascript:void(0);" class="btn btn-outline-primary" onclick="inviteParticipants({{ $poll->id }})">Deelnemers uitnodigen</a>
                <a href="javascript:void(0);" class="btn btn-outline-secondary" onclick="addDates({{ $poll->id }})">Datums toevoegen</a>
                <button type="button" class="btn btn-danger" onclick="endPoll({{ $poll->id }})">Poll beëindigen</button>
            </div>
        </div>
    @endif

    <!-- Card 1: Poll informatie + stemmen -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h2 class="card-title">{{ $poll->title }}</h2>
            <p><strong>Beschrijving:</strong> {{ $poll->description }}</p>
            <p><strong>Locatie:</strong> {{ $poll->location }}</p>

            <hr>

            <form @if($viewOnly) class="disabled-form" @else onsubmit="vote(event)" @endif>
                <input type="hidden" id="poll_id" value="{{ $poll->id }}">
                @if ($participant)
                <input type="hidden" id="participant_id" value="{{ $participant->id }}">
                @endif

                <div class="list-group">
                    @foreach($poll->pollDates as $date)
                        @php
                            $carbonDate = \Carbon\Carbon::parse($date->date);
                            $formatted = $carbonDate->translatedFormat('l j F Y'); // bijv. "maandag 10 juni 2025"
                            $voteCount = $votes[$date->date] ?? 0;
                        @endphp

                        <label class="list-group-item d-flex align-items-center">
                            <input class="form-check-input me-3 date-checkbox" type="checkbox" value="{{ $date->id }}" id="date{{ $date->id }}" @if($viewOnly) disabled @endif>
                            <div class="flex-grow-1">
                                <div class="fw-semibold">{{ $formatted }}</div>
                            </div>
                            <span class="badge bg-secondary ms-2">{{ $voteCount }} stemmen</span>
                        </label>
                    @endforeach
                </div>

                @if(!$viewOnly)
                    <button type="submit" class="btn btn-primary mt-3 w-100">Stem opslaan</button>
                @endif
            </form>
        </div>
    </div>

    <!-- Card 2: Weersvoorspelling -->
    <div class="card shadow-sm">
        <div class="card-body">
            <h2 class="card-title">Weersvoorspelling</h2>
            <div class="table-responsive">
                <table class="table table-bordered mt-3" id="weatherTable">
                    <thead>
                        <tr id="weatherTableHeader"></tr>
                    </thead>
                    <tbody>
                        <tr id="weatherTableTemperature"></tr>
                        <tr id="weatherTablePrecipitationProbability"></tr>
                        <tr id="weatherTablePrecipitationAmount"></tr>
                        <tr id="weatherTableWindSpeed"></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    async function vote(event) {
        event.preventDefault();

        const poll_id = document.getElementById('poll_id').value;
        const participant_id = document.getElementById('participant_id').value;
        const dates = Array.from(document.querySelectorAll('.date-checkbox'))
            .filter(cb => cb.checked)
            .map(cb => parseInt(cb.value));

        const response = await fetch(`/api/poll/${poll_id}/vote/${participant_id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ poll_id, participant_id, dates })
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

    async function endPoll(pollId) {
        if (!confirm("Weet je zeker dat je de poll wilt beëindigen?")) return;

        try {
            const response = await fetch(`/api/poll/${pollId}/end`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const result = await response.json();

            if (response.ok) {
                alert("Poll succesvol beëindigd.");
                location.reload();
            } else {
                alert("Fout bij beëindigen van poll: " + (result.message || "Onbekende fout"));
            }
        } catch (error) {
            console.error("Netwerkfout bij beëindigen van poll:", error);
        }
    }

    function inviteParticipants(pollId) {
        document.getElementById('invite_poll_id').value = pollId;
        new bootstrap.Modal(document.getElementById('inviteModal')).show();
    }

    async function submitInvite(event) {
        event.preventDefault();
        const pollId = document.getElementById('invite_poll_id').value;
        const emails = document.getElementById('invite_emails').value;

        try {
            const response = await fetch(`/api/poll/${pollId}/participants`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({emails})
            });

            const result = await response.json();

            if (response.ok) {
                bootstrap.Modal.getInstance(document.getElementById('inviteModal')).hide();
                alert("Deelnemer uitgenodigd.");
                location.reload();
            } else {
                alert("Fout: " + (result.message || "Onbekende fout"));
            }
        } catch (error) {
            console.error(error);
        }
    }

    function convertDatesToISO(input) {
    return input
        .split(',')
        .map(dateStr => {
            const [day, month, year] = dateStr.trim().split('-');
            return `${year}-${month}-${day}`;
        })
        .join(', ');
    }

    function addDates(pollId) {
        document.getElementById('date_poll_id').value = pollId;
        new bootstrap.Modal(document.getElementById('dateModal')).show();
    }

    async function submitDate(event) {
        event.preventDefault();
        const pollId = document.getElementById('date_poll_id').value;
        const date = convertDatesToISO(document.getElementById('new_date').value);

        try {
            const response = await fetch(`/api/poll/${pollId}/dates`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ dates: date})
            });

            const result = await response.json();

            if (response.ok) {
                bootstrap.Modal.getInstance(document.getElementById('dateModal')).hide();
                alert("Datum toegevoegd.");
                location.reload();
            } else {
                alert("Fout: " + (result.message || "Onbekende fout"));
            }
        } catch (error) {
            console.error(error);
        }
    }

    function fetchWeatherForecast() {
        fetch(`/api/weather/{{ $poll->location }}`)
            .then(response => response.json())
            .then(data => {
                const headerRow = document.getElementById('weatherTableHeader');
                const temperatureRow = document.getElementById('weatherTableTemperature');
                const precipitationProbabilityRow = document.getElementById('weatherTablePrecipitationProbability');
                const precipitationAmountRow = document.getElementById('weatherTablePrecipitationAmount');
                const windSpeedRow = document.getElementById('weatherTableWindSpeed');

                headerRow.innerHTML = '<th>Dag</th>';
                temperatureRow.innerHTML = '<td><strong>Temperatuur</strong></td>';
                precipitationProbabilityRow.innerHTML = '<td><strong>Neerslagkans</strong></td>';
                precipitationAmountRow.innerHTML = '<td><strong>Neerslaghoeveelheid</strong></td>';
                windSpeedRow.innerHTML = '<td><strong>Windsnelheid</strong></td>';

                data.forEach(entry => {
                    const day = new Date(entry.day).toLocaleDateString('nl-NL', { weekday: 'long', day: 'numeric', month: 'long' });

                    headerRow.innerHTML += `<th>${day}</th>`;
                    temperatureRow.innerHTML += `<td>${entry.temperature.toFixed(1)} °C</td>`;
                    precipitationProbabilityRow.innerHTML += `<td>${entry.precipitationProbability.toFixed(0)}%</td>`;
                    precipitationAmountRow.innerHTML += `<td>${entry.rainAccumulation.toFixed(1)} mm</td>`;
                    windSpeedRow.innerHTML += `<td>${entry.windSpeed.toFixed(1)} m/s</td>`;
                });
            })
            .catch(error => console.error('Error fetching weather data:', error));
    }
    // fetchWeatherForecast();
</script>

</body>
</html>

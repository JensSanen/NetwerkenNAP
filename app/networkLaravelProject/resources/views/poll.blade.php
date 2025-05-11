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
                <input type="hidden" id="invite_poll_id" name="poll_id" value="{{ $poll->id }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="inviteModalLabel">Deelnemer uitnodigen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="email-inputs">
                        <input type="email" name="emails[]" class="form-control mb-2" required>
                    </div>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="addEmailInput()">+ E-mail</button>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Verstuur uitnodiging</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Datum toevoegen -->
    <div class="modal fade" id="addDateModal" tabindex="-1" aria-labelledby="addDateModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="addDateForm" class="modal-content" onsubmit="submitDates(event)">
                <input type="hidden" id="date_poll_id" name="poll_id" value="{{ $poll->id }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="addDateModalLabel">Datum toevoegen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="date-inputs">
                        <input type="date" name="dates[]" class="form-control mb-2" required>
                    </div>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="addDateInput()">+ Datum</button>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Voeg toe</button>
                </div>
            </form>
        </div>
    </div>

    @if($viewOnly)
        <div class="alert alert-info">
            De poll is afgelopen, je kunt niet meer stemmen.
        </div>
    @endif

    @if($isCreator && !$viewOnly)
        <div class="card mb-4">
            <div class="card-body">
                <h5>Je bent de maker van deze poll</h5>
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
                            $formatted = $carbonDate->translatedFormat('l j F Y');
                            $voteCount = $votes[$date->date] ?? 0;
                            $hasVoted = array_key_exists($date->id, $participantVotes) && $participantVotes[$date->id] !== null;
                        @endphp

                        <label class="list-group-item d-flex align-items-center">
                            <input class="form-check-input me-3 date-checkbox" type="checkbox"
                                value="{{ $date->id }}"
                                id="date{{ $date->id }}"
                                @if($viewOnly) disabled @endif
                                @if($hasVoted) checked @endif>

                            <div class="flex-grow-1">
                                <div class="fw-semibold">{{ $formatted }}</div>
                            </div>
                            @if ($viewOnly || $isCreator || $poll->show_votes)
                                <span class="badge bg-secondary ms-2">{{ $voteCount }} stemmen</span>
                            @endif
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
    function inviteParticipants(pollId) {
        document.getElementById('invite_poll_id').value = pollId;
        new bootstrap.Modal(document.getElementById('inviteModal')).show();
    }

    function addEmailInput() {
        const container = document.getElementById('email-inputs');
        const input = document.createElement('input');
        input.type = 'email';
        input.name = 'emails[]';
        input.className = 'form-control mb-2';
        container.appendChild(input);
    }

    async function submitInvite(event) {
        event.preventDefault();
        const form = event.target;
        const emailInputs = form.querySelectorAll('input[name="emails[]"]');
        const emails = Array.from(emailInputs).map(input => input.value).filter(Boolean);

        console.log(emails);

        try {
            const response = await fetch('/api/poll/{{ $poll->id }}/addParticipants', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ emails, vote_token: "{{ $participant->vote_token }}" })
            });

            const data = await response.json();

            if (!response.ok) {
                let errorMsg = data.message || 'Onbekende fout';
                if (data.errors) {
                    errorMsg = Object.values(data.errors).flat().join('\n');
                }
                throw new Error(errorMsg);
            }

            form.reset();
            alert('Uitnodigingen verzonden!');
        } catch (error) {
            console.error('Fout bij submitInvite:', error);
            alert('Fout bij verzenden uitnodiging:\n' + error.message);
        }
    }

    function addDates(pollId) {
        document.getElementById('date_poll_id').value = pollId;
        new bootstrap.Modal(document.getElementById('addDateModal')).show();
    }

    function addDateInput() {
        const container = document.getElementById('date-inputs');
        const input = document.createElement('input');
        input.type = 'date';
        input.name = 'dates[]';
        input.className = 'form-control mb-2';
        container.appendChild(input);
    }

    async function submitDates(event) {
        event.preventDefault();
        const form = event.target;
        const dateInputs = form.querySelectorAll('input[name="dates[]"]');
        const dates = Array.from(dateInputs).map(input => input.value).filter(Boolean);

        console.log(dates);

        try {
            const response = await fetch('/api/poll/{{ $poll->id }}/addDates', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ dates, vote_token: "{{ $participant->vote_token }}" })
            });

            const data = await response.json();

            if (!response.ok) {
                let errorMsg = data.message || 'Onbekende fout';
                if (data.errors) {
                    errorMsg = Object.values(data.errors).flat().join('\n');
                }
                throw new Error(errorMsg);
            }

            form.reset();
            alert('Datums toegevoegd!');
            location.reload(); // herladen om nieuwe datums weer te geven
        } catch (error) {
            console.error('Fout bij submitDates:', error);
            alert('Fout bij toevoegen datums:\n' + error.message);
        }
    }

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
            body: JSON.stringify({ dates, vote_token: "{{ $participant->vote_token }}" })
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
                },
                body: JSON.stringify({ vote_token: "{{ $participant->vote_token }}" })
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
    fetchWeatherForecast();
</script>

</body>
</html>

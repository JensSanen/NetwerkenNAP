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
                    <span class="badge bg-secondary ms-2">{{ $votes[$date->date] ?? 0 }} stemmen</span>
                </label>
            </div>
        @endforeach

        <button type="submit" class="btn btn-primary mt-3">Stem opslaan</button>
    </form>
</div>

<div class="container mt-5">
    <h2 id= WeatherForecastLabel>Weersvoorspelling</h2>
    <table class="table table-bordered" id="weatherTable">
        <thead>
            <tr id="weatherTableHeader">
                <!-- Headers worden dynamisch toegevoegd -->
            </tr>
        </thead>
        <tbody>
            <tr id="weatherTableTemperature"></tr>
            <tr id="weatherTablePrecipitationProbability"></tr>
            <tr id="weatherTablePrecipitationAmount"></tr>
            <tr id="weatherTableWindSpeed"></tr>
            <!-- Rijen worden dynamisch toegevoegd -->
        </tbody>
    </table>
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

    function fetchWeatherForecast() {
        fetch(`/api/weather/{{ $poll->location }}`)
            .then(response => response.json())
            .then(data => {
                const headerRow = document.getElementById('weatherTableHeader');
                const temperatureRow = document.getElementById('weatherTableTemperature');
                const precipitationProbabilityRow = document.getElementById('weatherTablePrecipitationProbability');
                const precipitationAmountRow = document.getElementById('weatherTablePrecipitationAmount');
                const windSpeedRow = document.getElementById('weatherTableWindSpeed');

                // Maak de tabel leeg voordat nieuwe data wordt toegevoegd
                headerRow.innerHTML = '';
                temperatureRow.innerHTML = '';
                precipitationProbabilityRow.innerHTML = '';
                precipitationAmountRow.innerHTML = '';
                windSpeedRow.innerHTML = '';


                const headers = ['Dag', 'Temperatuur', 'Neerslagkans', 'Neerslaghoeveelheid', 'Windsnelheid'];

                headers.forEach(header => {
                    if (header === 'Dag') {
                        const th = document.createElement('th');
                        th.textContent = header;
                        headerRow.appendChild(th);;
                    }
                    if (header === 'Temperatuur') {
                        const td = document.createElement('td');
                        td.textContent = header;
                        temperatureRow.appendChild(td);
                    }
                    if (header === 'Neerslagkans') {
                        const td = document.createElement('td');
                        td.textContent = header;
                        precipitationProbabilityRow.appendChild(td);
                    }
                    if (header === 'Neerslaghoeveelheid') {
                        const td = document.createElement('td');
                        td.textContent = header;
                        precipitationAmountRow.appendChild(td);
                    }
                    if (header === 'Windsnelheid') {
                        const td = document.createElement('td');
                        td.textContent = header;
                        windSpeedRow.appendChild(td);
                    }
                    data.forEach(entry => {
                        if (header === 'Dag') {
                        const day = new Date(entry.day).toLocaleDateString('nl-NL', { weekday: 'long', day: 'numeric', month: 'long' });
                        const th = document.createElement('th');
                        th.textContent = day;
                        headerRow.appendChild(th);
                        }
                        if (header === 'Temperatuur') {
                        const temperature = `${entry.temperature.toFixed(1)} °C`;
                        const td = document.createElement('td');
                        td.textContent = temperature;
                        temperatureRow.appendChild(td);
                        }
                        if (header === 'Neerslagkans') {
                        const precipitation = `${entry.precipitationProbability.toFixed(0)}%`;
                        const td = document.createElement('td');
                        td.textContent = precipitation;
                        precipitationProbabilityRow.appendChild(td);
                        }
                        if (header === 'Neerslaghoeveelheid') {
                        const precipitationAmount = `${entry.rainAccumulation.toFixed(1)} mm`;
                        const td = document.createElement('td');
                        td.textContent = precipitationAmount;
                        precipitationAmountRow.appendChild(td);
                        }
                        if (header === 'Windsnelheid') {
                        const windSpeed = `${entry.windSpeed.toFixed(1)} m/s`;
                        const td = document.createElement('td');
                        td.textContent = windSpeed;
                        windSpeedRow.appendChild(td);
                        }
                    });

                });

            })
            .catch(error => console.error('Error fetching weather data:', error));
    }

    // fetchWeatherForecast();
</script>

</body>
</html>

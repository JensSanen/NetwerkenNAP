<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Poll Planner</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h1 class="text-center mb-4">📅 Poll Planner</h1>

            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    Nieuwe poll aanmaken
                </div>
                <div class="card-body">
                    <form id="create_poll" onsubmit="createPoll(event)" novalidate>
                        @csrf
                        <div class="mb-3">
                            <label for="email_creator" class="form-label">Jouw e-mailadres</label>
                            <input type="email" name="email_creator" id="email_creator" class="form-control" placeholder="piet@example.com" required>
                        </div>

                        <div class="mb-3">
                            <label for="title" class="form-label">Titel van de poll</label>
                            <input type="text" name="title" id="title" class="form-control" placeholder="Bijv. BBQ op zondag" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Beschrijving</label>
                            <textarea name="description" id="description" class="form-control" rows="3" placeholder="Bijv. BBQ bij mij in de tuin" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="location" class="form-label">Locatie</label>
                            <input type="text" name="location" id="location" class="form-control" placeholder="Bijv. Hasselt" required>
                        </div>

                        <div class="mb-3">
                            <label for="dates" class="form-label">Beschikbare datums (gescheiden door komma's)</label>
                            <input type="text" name="dates" id="dates" class="form-control" placeholder="01-06-2025, 02-06-2025, 03-06-2025" pattern="\d{2}-\d{2}-\d{4}(,\s*\d{2}-\d{2}-\d{4})*" required>
                        </div>

                        <div class="mb-3">
                            <label for="emails" class="form-label">E-mailadressen deelnemers (gescheiden door komma's)</label>
                            <textarea name="emails" id="emails" class="form-control" rows="3" placeholder="jan@example.com, lisa@example.com" required></textarea>
                        </div>

                        <button type="submit" class="btn btn-success w-100">Poll aanmaken</button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    function convertDatesToISO(input) {
    return input
        .split(',')
        .map(dateStr => {
            const [day, month, year] = dateStr.trim().split('-');
            return `${year}-${month}-${day}`;
        })
        .join(', ');
    }

    async function createPoll(event) {
        event.preventDefault();

        const form = event.target;

        const formData = {
            email_creator: form.email_creator.value,
            title: form.title.value,
            description: form.description.value,
            location: form.location.value,
            dates: convertDatesToISO(form.dates.value),
            emails: form.emails.value
        };

        console.log('Form data:', formData);

        try {
            const response = await fetch('/api/poll', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(formData)
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
            alert('Poll succesvol aangemaakt!');
        } catch (error) {
            console.error('Fout bij createPoll:', error);
            alert('Er is een fout opgetreden:\n' + error.message);
        }
    }
</script>

</body>
</html>

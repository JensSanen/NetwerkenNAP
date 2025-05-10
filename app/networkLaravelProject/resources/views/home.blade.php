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
                            <input type="email" name="email_creator" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="title" class="form-label">Titel</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Beschrijving</label>
                            <textarea name="description" class="form-control"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="location" class="form-label">Locatie</label>
                            <input type="text" name="location" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Datums</label>
                            <div id="date-inputs">
                                <input type="date" name="dates[]" class="form-control mb-2">
                            </div>
                            <button type="button" class="btn btn-sm btn-secondary" onclick="addDateInput()">+ Datum</button>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">E-mails van deelnemers</label>
                            <div id="email-inputs">
                                <input type="email" name="emails[]" class="form-control mb-2">
                            </div>
                            <button type="button" class="btn btn-sm btn-secondary" onclick="addEmailInput()">+ E-mail</button>
                        </div>

                        <button type="submit" class="btn btn-primary">Poll aanmaken</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function addDateInput() {
        const container = document.getElementById('date-inputs');
        const input = document.createElement('input');
        input.type = 'date';
        input.name = 'dates[]';
        input.className = 'form-control mb-2';
        container.appendChild(input);
    }

    function addEmailInput() {
        const container = document.getElementById('email-inputs');
        const input = document.createElement('input');
        input.type = 'email';
        input.name = 'emails[]';
        input.className = 'form-control mb-2';
        container.appendChild(input);
    }

    async function createPoll(event) {
        event.preventDefault();

        const form = event.target;

        // Verzamel alle datums
        const dateInputs = form.querySelectorAll('input[name="dates[]"]');
        const dates = Array.from(dateInputs)
            .map(input => input.value)
            .filter(date => date); // Verwijder lege invoer

        // Verzamel alle e-mails
        const emailInputs = form.querySelectorAll('input[name="emails[]"]');
        const emails = Array.from(emailInputs)
            .map(input => input.value)
            .filter(email => email); // Verwijder lege invoer

        const formData = {
            email_creator: form.email_creator.value,
            title: form.title.value,
            description: form.description.value,
            location: form.location.value,
            dates: dates,
            emails: emails
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

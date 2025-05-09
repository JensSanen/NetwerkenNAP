<!DOCTYPE html>
<html>
<head>
    <title>Poll Planner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h1 class="text-center mb-4">📅 Poll Planner</h1>

            <div class="row">
                <!-- Linker card -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            Nieuwe poll aanmaken
                        </div>
                        <div class="card-body">
                            <form id="create_poll" onsubmit="createPoll(event)">
                                <div class="mb-3">
                                    <label for="email_creator" class="form-label">Jouw E-mailadres</label>
                                    <input type="text" name="email_creator" id="email_creator" class="form-control" placeholder="piet@example.com" required>
                                </div>
                                <div class="mb-3">
                                    <label for="title" class="form-label">Titel van de poll</label>
                                    <input type="text" name="title" id="title" class="form-control" placeholder="Bijv. BBQ op zondag" required>
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Beschrijving</label>
                                    <textarea name="description" id="description" rows="3" class="form-control" placeholder="Bijv. BBQ bij mij in de tuin" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="location" class="form-label">Locatie</label>
                                    <input type="text" name="location" id="location" class="form-control" placeholder="Bijv. Hasselt" required>
                                </div>
                                <div class="mb-3">
                                    <label for="dates" class="form-label">Beschikbare datums (gescheiden door komma's)</label>
                                    <input type="text" name="dates" id="dates" class="form-control" placeholder="2025-06-01, 2025-06-02, 2025-06-03" required>
                                </div>
                                <div class="mb-3">
                                    <label for="emails" class="form-label">E-mailadressen deelnemers (gescheiden door komma's)</label>
                                    <textarea name="emails" id="emails" rows="3" class="form-control" placeholder="jan@example.com, lisa@example.com" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-success">Poll aanmaken</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Rechter card -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header bg-secondary text-white">
                            Bestaande poll bekijken
                        </div>
                        <div class="card-body">
                            <form action="" method="GET">
                                <div class="mb-3">
                                    <label for="poll_id" class="form-label">Poll-ID</label>
                                    <input type="text" name="poll_id" id="poll_id" class="form-control" placeholder="Bijv. 1" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Bekijk poll</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
<script>
    async function createPoll(event) {
        event.preventDefault();

        const emailCreator = document.getElementById('email_creator').value;
        const title = document.getElementById('title').value;
        const description = document.getElementById('description').value;
        const location = document.getElementById('location').value;
        const dates = document.getElementById('dates').value;
        const emails = document.getElementById('emails').value;

        try {
            const response = await fetch('/api/poll/create', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    email_creator: emailCreator,
                    title: title,
                    description: description,
                    location: location,
                    dates: dates,
                    emails: emails
                })
            });

            const data = await response.json();

            if (!response.ok) {
                // Toon validatiefouten of algemene fout
                if (data.errors) {
                    const messages = Object.values(data.errors).flat().join('\n');
                    throw new Error(messages);
                } else {
                    throw new Error(data.message || 'Onbekende fout');
                }
            }

            // Succes
            document.getElementById('create_poll').reset();
            alert('Poll succesvol aangemaakt!');
        } catch (error) {
            console.error('Fout bij createPoll:', error);
            alert('Er is een fout opgetreden:\n' + error.message);
        }
    }
</script>
</html>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Nieuwe stem in poll</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f6f6f6; padding: 30px;">
    <div style="max-width: 600px; margin: auto; background-color: #ffffff; border-radius: 8px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
        <h2 style="color: #333333; margin-top: 0;">📥 Nieuwe stem in poll "{{ $poll->title }}"</h2>

        <p style="font-size: 16px; color: #555555;">
            De deelnemer <strong>{{ $participant->email }}</strong> heeft zojuist gestemd in jouw poll.
        </p>

        <p style="font-size: 16px; color: #555555;">
            Je kunt de bijgewerkte resultaten bekijken via onderstaande knop:
        </p>

        <p style="text-align: center; margin: 30px 0;">
            <a href="{{ $creatorUrl }}"
               style="background-color: #0d6efd; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block; font-weight: bold;">
                Bekijk pollresultaten
            </a>
        </p>
    </div>
</body>
</html>

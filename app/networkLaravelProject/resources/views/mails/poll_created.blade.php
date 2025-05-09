<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Jouw poll is succesvol aangemaakt!</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f6f6f6; margin: 0; padding: 20px;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 600px; margin: auto; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.05);">
        <tr>
            <td>
                <h2 style="color: #333333;">🎉 Je poll is succesvol aangemaakt!</h2>
                <p style="font-size: 16px; color: #555555;">
                    Bedankt voor het aanmaken van een poll via onze service. Je kunt de poll beheren via onderstaande link:
                </p>
                <p style="margin: 20px 0;">
                    <a href="{{ $creator_url }}" style="display: inline-block; background-color: #4CAF50; color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px; font-size: 16px;">
                        Beheer je poll
                    </a>
                </p>
                <p style="font-size: 14px; color: #888888;">
                    Als je deze poll niet hebt aangemaakt, kun je deze e-mail negeren.
                </p>
                <hr style="margin-top: 30px; border: none; border-top: 1px solid #eeeeee;">
                <p style="font-size: 12px; color: #aaaaaa; text-align: center;">
                    &copy; {{ date('Y') }} JouwPollPlatform. Alle rechten voorbehouden.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>

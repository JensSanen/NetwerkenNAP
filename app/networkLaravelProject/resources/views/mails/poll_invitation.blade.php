<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Uitnodiging om te stemmen</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f6f6f6; margin: 0; padding: 20px;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0"
           style="max-width: 600px; margin: auto; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.05);">
        <tr>
            <td>
                <h2 style="color: #333333;">🗳️ Je bent uitgenodigd om te stemmen</h2>
                <p style="font-size: 16px; color: #555555;">
                    Hallo, je bent uitgenodigd om je voorkeur aan te geven voor een datum voor het volgende evenement:
                </p>

                <table cellpadding="0" cellspacing="0" border="0" style="margin: 20px 0; font-size: 15px; color: #444;">
                    <tr>
                        <td style="padding: 5px 0;"><strong>📌 Titel:</strong></td>
                        <td style="padding: 5px 10px;">{{ $participant->poll->title }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0;"><strong>📍 Locatie:</strong></td>
                        <td style="padding: 5px 10px;">{{ $participant->poll->location }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0;"><strong>📝 Beschrijving:</strong></td>
                        <td style="padding: 5px 10px;">{{ $participant->poll->description }}</td>
                    </tr>
                </table>

                <p style="margin: 20px 0;">
                    <a href="{{ $voteUrl }}" style="display: inline-block; background-color: #28a745; color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px; font-size: 16px;">
                        Stem nu
                    </a>
                </p>
            </td>
        </tr>
    </table>
</body>
</html>

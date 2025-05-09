<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Poll beëindigd</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f6f6f6; padding: 30px;">
    <div style="max-width: 600px; margin: auto; background-color: #ffffff; border-radius: 8px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
        <h2 style="color: #333333; margin-top: 0;">🎉 De poll "{{ $poll->title }}" is afgerond</h2>

        <p style="font-size: 16px; color: #555555;">
            De stemming is afgesloten en de best gekozen datum is:
        </p>

        <p style="font-size: 20px; font-weight: bold; color: #0d6efd; margin: 20px 0;">
            {{ \Carbon\Carbon::parse($finalDate)->format('d-m-Y') }}
        </p>

        <p style="font-size: 16px; color: #555555;">
            Je kunt de resultaten bekijken via de onderstaande knop:
        </p>

        <p style="text-align: center; margin: 30px 0;">
            <a href="{{ url('/poll/' . $poll->id) }}"
               style="background-color: #0d6efd; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block; font-weight: bold;">
                Bekijk resultaten
            </a>
        </p>

        <p style="font-size: 14px; color: #999999; text-align: center;">
            Of kopieer en plak deze link in je browser:<br>
            <a href="{{ url('/poll/' . $poll->id) }}">{{ url('/poll/' . $poll->id) }}</a>
        </p>

        <hr style="margin: 30px 0; border: none; border-top: 1px solid #eeeeee;">

        <p style="font-size: 12px; color: #999999; text-align: center;">
            Deze e-mail is automatisch verzonden na afloop van de poll.<br>
            &copy; {{ date('Y') }} Pollsysteem
        </p>
    </div>
</body>
</html>

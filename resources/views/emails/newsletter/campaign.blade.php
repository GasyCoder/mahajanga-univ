<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $campaign->subject }}</title>
</head>
<body style="margin:0;padding:0;background:#f6f7fb;font-family:Arial,Helvetica,sans-serif;">
  <div style="max-width:680px;margin:0 auto;padding:24px;">
    <div style="background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e9ecf3;">
      <div style="padding:18px 20px;background:#0b1b3a;color:#ffffff;">
        <div style="font-size:14px;opacity:.9;">Université de Mahajanga</div>
        <div style="font-size:18px;font-weight:700;margin-top:6px;">{{ $campaign->subject }}</div>
      </div>

      <div style="padding:20px;color:#111827;line-height:1.6;">
        {!! $campaign->content_html !!}
      </div>

      <div style="padding:14px 20px;border-top:1px solid #e9ecf3;color:#6b7280;font-size:12px;">
        <div>Vous recevez cet email car vous êtes abonné(e) à la newsletter.</div>
        <div style="margin-top:6px;">
          Se désabonner : <a href="{{ $unsubscribeUrl }}" style="color:#2563eb;">{{ $unsubscribeUrl }}</a>
        </div>
      </div>
    </div>

    <div style="text-align:center;color:#9ca3af;font-size:12px;margin-top:12px;">
      © {{ date('Y') }} Université de Mahajanga
    </div>
  </div>
</body>
</html>
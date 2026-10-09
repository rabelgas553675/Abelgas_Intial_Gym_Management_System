<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>{{ $title ?? 'QR Card' }} – APEX Fitness Gym</title>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --accent: #e0a93b;          /* APEX gold – matches the rest of the app */
      --accent-soft: rgba(224,169,59,.14);
      --card-bg: #0c0c0c;
      --card-bg-2: #151515;
      --line: #262626;
      --text: #f4f1ea;
      --muted: #9a9486;           /* readable on the dark card (old #555/#333 were not) */
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      background: #ece9e2;
      font-family: 'DM Sans', sans-serif;
      color: #111;
      padding: 40px 20px 60px;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;   /* keep the dark card when printing */
    }

    /* ───────── Top controls ───────── */
    .controls {
      max-width: 1100px;
      margin: 0 auto 36px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 16px;
      flex-wrap: wrap;
    }
    .controls h1 {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 34px;
      letter-spacing: 2px;
      font-weight: 400;
    }
    .controls h1 span { color: var(--accent); }
    .btn-group { display: flex; gap: 12px; }
    .btn {
      padding: 11px 22px;
      border-radius: 10px;
      border: 1px solid transparent;
      cursor: pointer;
      font: 600 14px 'DM Sans', sans-serif;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      transition: transform .15s ease, box-shadow .15s ease;
    }
    .btn:hover { transform: translateY(-1px); }
    .btn-print { background: var(--accent); color: #111; box-shadow: 0 6px 16px rgba(224,169,59,.35); }
    .btn-back  { background: #fff; color: #111; border-color: #d9d5ca; }

    /* ───────── Grid (cards are centered, even when there's only one) ───────── */
    .card-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, 340px);
      justify-content: center;
      gap: 32px;
      max-width: 1100px;
      margin: 0 auto;
    }

    /* ───────── ID card ───────── */
    .id-card {
      --role: var(--accent);
      width: 340px;
      background:
        radial-gradient(circle at 100% 0%, color-mix(in srgb, var(--role) 14%, transparent), transparent 55%),
        linear-gradient(180deg, var(--card-bg-2) 0%, var(--card-bg) 55%);
      border: 1px solid var(--line);
      border-radius: 22px;
      overflow: hidden;
      box-shadow: 0 18px 40px rgba(0,0,0,.28);
      position: relative;
    }
    .role-admin      { --role: #e0a93b; }
    .role-staff      { --role: #fb923c; }
    .role-instructor { --role: #60a5fa; }
    .role-member     { --role: #4ade80; }

    /* Header */
    .card-header {
      padding: 22px 24px 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: relative;
    }
    .card-header::after {
      content: '';
      position: absolute; left: 0; right: 0; bottom: 0; height: 3px;
      background: linear-gradient(90deg, var(--role), transparent);
    }
    .brand { display: flex; align-items: center; gap: 10px; }
    .brand-icon {
      width: 34px; height: 34px; border-radius: 9px;
      background: var(--role);
      display: flex; align-items: center; justify-content: center;
    }
    .brand-icon svg { width: 19px; height: 19px; }
    .brand-name {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 24px; letter-spacing: 3px; color: var(--text);
      line-height: 1;
    }
    .brand-name small {
      display: block;
      font: 600 8px 'DM Sans', sans-serif;
      letter-spacing: 2.5px; color: var(--muted); margin-top: 3px;
    }
    .role-badge {
      font-size: 10px; font-weight: 700; letter-spacing: 1.5px;
      text-transform: uppercase;
      padding: 5px 12px; border-radius: 999px;
      color: var(--role);
      background: color-mix(in srgb, var(--role) 14%, transparent);
      border: 1px solid color-mix(in srgb, var(--role) 40%, transparent);
    }

    /* Body */
    .card-body { padding: 24px 24px 22px; }

    .user-info { text-align: center; margin-bottom: 20px; }
    .user-name {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 30px; font-weight: 400; letter-spacing: 2px;
      color: var(--text); line-height: 1.1;
      word-break: break-word;
    }
    .user-id {
      display: inline-block; margin-top: 8px;
      font-size: 11px; font-weight: 600; letter-spacing: 1.5px;
      color: var(--role);
      background: color-mix(in srgb, var(--role) 10%, transparent);
      padding: 4px 12px; border-radius: 6px;
    }

    /* QR */
    .qr-section {
      background: #fff;
      border-radius: 16px;
      padding: 18px 16px 14px;
      text-align: center;
      box-shadow: inset 0 0 0 1px #eee;
    }
    .qr-section img { width: 170px; height: 170px; display: block; margin: 0 auto; }
    .no-qr {
      width: 170px; height: 170px; margin: 0 auto;
      display: flex; align-items: center; justify-content: center;
      color: #999; font-size: 12px; text-align: center;
      border: 2px dashed #ddd; border-radius: 10px;
    }
    .qr-id {
      margin-top: 10px;
      font-family: 'Bebas Neue', sans-serif;
      font-size: 15px; letter-spacing: 2.5px; color: #111;
      word-break: break-all;
    }
    .scan-hint {
      text-align: center; margin-top: 14px;
      font-size: 10px; letter-spacing: 1.8px; text-transform: uppercase;
      color: var(--muted);
    }

    /* Footer */
    .card-footer {
      padding: 13px 24px;
      display: flex; align-items: center; justify-content: space-between;
      border-top: 1px solid var(--line);
      background: rgba(0,0,0,.35);
    }
    .footer-text {
      font-size: 10px; font-weight: 600; letter-spacing: 1.5px;
      text-transform: uppercase; color: var(--muted);
    }
    .footer-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--role); }

    /* ───────── Print ───────── */
    @page { margin: 12mm; }
    @media print {
      body { background: #fff; padding: 0; }
      .controls { display: none !important; }
      .card-grid {
        grid-template-columns: repeat(2, 340px);
        gap: 20px;
        justify-content: center;
      }
      .id-card {
        box-shadow: none;
        break-inside: avoid;
        page-break-inside: avoid;
      }
    }

    @media (max-width: 420px) {
      .card-grid { grid-template-columns: 1fr; }
      .id-card { width: 100%; }
    }
  </style>
</head>
<body>

  <div class="controls">
    <h1>{{ $title ?? 'QR Card' }}</h1>
    <div class="btn-group">
      <a href="{{ route('attendance.qr-list') }}" class="btn btn-back">← QR List</a>
      <button type="button" class="btn btn-print" onclick="window.print()">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <polyline points="6 9 6 2 18 2 18 9"/>
          <path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/>
          <rect x="6" y="14" width="12" height="8"/>
        </svg>
        Print QR Card
      </button>
    </div>
  </div>

  <div class="card-grid">
    @php
        $cards = $items ?? collect();
        if ($cards->isEmpty() && isset($person)) {
            $cards = collect([$person]);
        }
    @endphp

    @foreach($cards as $card)
      @php
        $isMemberCard = $card instanceof \App\Models\Member;
        $name    = $card->name ?? ($card->user->name ?? 'Unknown');
        $role    = strtolower($isMemberCard ? 'member' : ($card->role ?? ($card->user->role ?? 'staff')));
        $roleClass = in_array($role, ['admin', 'staff', 'instructor', 'member']) ? $role : 'staff';

        $qrPath  = $card->qr_code_path ?? ($card->qrToken?->qr_code_path ?? null);
        $qrToken = $card->qr_token ?? ($card->qrToken?->qr_token ?? null);

        $idLabel = $isMemberCard ? 'MEMBER ID' : strtoupper($role) . ' ID';
        $idValue = $card->id ?? $card->user_id;
      @endphp

      <div class="id-card role-{{ $roleClass }}">
        <div class="card-header">
          <div class="brand">
            <div class="brand-icon">
              <svg fill="none" stroke="#111" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
              </svg>
            </div>
            <div class="brand-name">APEX<small>FITNESS GYM</small></div>
          </div>
          <span class="role-badge">{{ strtoupper($role) }}</span>
        </div>

        <div class="card-body">
          <div class="user-info">
            <div class="user-name">{{ $name }}</div>
            <div class="user-id">{{ $idLabel }} · {{ str_pad((string) $idValue, 4, '0', STR_PAD_LEFT) }}</div>
          </div>

          <div class="qr-section">
            @if($qrPath)
              <img src="{{ asset('storage/' . $qrPath) }}" alt="QR Code for {{ $name }}">
            @else
              <div class="no-qr">No QR<br>Generated</div>
            @endif
            <div class="qr-id">{{ $qrToken ?? 'No QR Token' }}</div>
          </div>

          <div class="scan-hint">Scan at the entrance to check in</div>
        </div>

        <div class="card-footer">
          <span class="footer-text">APEX {{ $isMemberCard ? 'Member' : ucfirst($role) }}</span>
          <div class="footer-dot"></div>
          <span class="footer-text">{{ now()->format('Y') }}</span>
        </div>
      </div>
    @endforeach
  </div>

</body>
</html>
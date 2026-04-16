<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>QR Cards - Staff List</title>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
        --primary: #c8ff00;
        --primary-dark: #80cc00;
        --surface: #0a0a0a;
        --border: #222;
        --muted: #555;
    }

    *{box-sizing:border-box;margin:0;padding:0;}
    body{
      background:#f0f0f0;
      font-family:'DM Sans',sans-serif;
      padding:40px 20px;
    }

    .controls{
      max-width: 1100px;
      margin: 0 auto 30px auto;
      display:flex;justify-content: space-between; align-items: center;
    }
    .btn-group { display: flex; gap: 12px; }
    .btn{
      padding:10px 24px;border-radius:8px;border:none;cursor:pointer;
      font-family:'DM Sans',sans-serif;font-size:14px;font-weight:600;
      text-decoration:none;display:inline-flex;align-items:center;gap:6px;
    }
    .btn-print{background:#111;color:#fff;}
    .btn-back {background:#fff;color:#111;border:1px solid #ddd;}

    /* GRID LAYOUT */
    .card-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
      gap: 30px;
      max-width: 1100px;
      margin: 0 auto;
    }

    /* ID CARD */
    .id-card{
      width:340px;
      background: var(--surface);
      border-radius:20px;
      overflow:hidden;
      box-shadow:0 10px 30px rgba(0,0,0,0.2);
      position:relative;
      margin: 0 auto;
    }

    /* Card header */
    .card-header{
      background:linear-gradient(135deg,#1a1a1a 0%,#0d0d0d 100%);
      padding:22px 24px 18px;
      border-bottom:1px solid #222;
      position:relative;
    }
    .card-header::after{
      content:'';position:absolute;bottom:0;left:0;right:0;
      height:3px;
      background:linear-gradient(90deg, var(--primary), var(--primary-dark));
    }
    .brand{
      display:flex;align-items:center;gap:10px;
    }
    .brand-icon{
      width:32px;height:32px;background: var(--primary);border-radius:7px;
      display:flex;align-items:center;justify-content:center;flex-shrink:0;
    }
    .brand-icon svg{width:18px;height:18px;}
    .brand-name{
      font-family:'Bebas Neue',sans-serif;font-size:20px;
      color: var(--primary);letter-spacing:3px;
    }

    /* Role badge */
    .role-badge{
      position:absolute;top:22px;right:24px;
      font-size:9px;font-weight:700;padding:3px 10px;border-radius:4px;
      letter-spacing:1.5px;text-transform:uppercase;
    }
    .role-admin     {background:rgba(200,255,0,0.12);color:#c8ff00;border:1px solid rgba(200,255,0,0.3);}
    .role-staff     {background:rgba(251,191,36,0.12);color:#fbbf24;border:1px solid rgba(251,191,36,0.3);}
    .role-instructor{background:rgba(255,107,53,0.12);color:#ff6b35;border:1px solid rgba(255,107,53,0.3);}

    /* Card body */
    .card-body{padding:24px;}

    .user-info {
        text-align: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #1e1e1e;
    }
    .user-name{
      font-size:18px;font-weight:700;color:#f0f0f0;text-transform:uppercase;
      letter-spacing:1px;margin-bottom:4px;
    }
    .user-id{
      font-size:10px;color: var(--muted);letter-spacing:1px;
    }

    /* QR section */
    .qr-section{
      background:#fff;
      border-radius:12px;
      padding:16px;
      text-align:center;
    }
    .qr-section img {
      width:160px;height:160px;
      display:block;margin:0 auto;
    }
    .no-qr {
        width:160px;height:160px;display:flex;align-items:center;
        justify-content:center;color:#ccc;font-size:12px;margin:0 auto;
    }
    .qr-id{
      margin-top:10px;
      font-family:'Bebas Neue',sans-serif;
      font-size:14px;letter-spacing:2px;
      color:#111;
    }

    /* Card footer */
    .card-footer{
      background:#0d0d0d;padding:12px 24px;
      display:flex;align-items:center;justify-content:space-between;
      border-top:1px solid #1a1a1a;
    }
    .footer-text{font-size:9px;color:#333;letter-spacing:1px;text-transform:uppercase;}
    .footer-dot{width:6px;height:6px;border-radius:50%;background: var(--primary);}

    /* Print styles */
    @media print {
      body{background:#fff;padding:0;}
      .controls{display:none;}
      .card-grid { display: block; }
      .id-card{
        box-shadow:none;
        border:1px solid #ddd;
        page-break-inside:avoid;
        margin-bottom: 20px;
      }
    }
  </style>
</head>
<body>

  <div class="controls">
    <h1 style="font-family:'Bebas Neue'; letter-spacing:2px;">Staff QR Directory</h1>
    <div class="btn-group">
        <a href="{{ route('attendance.index') }}" class="btn btn-back">← Dashboard</a>
        <button class="btn btn-print" onclick="window.print()">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polyline points="6 9 6 2 18 2 18 9"/>
                <path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/>
                <rect x="6" y="14" width="12" height="8"/>
            </svg>
            Print All Cards
        </button>
    </div>
  </div>

  <div class="card-grid">
    @foreach($staffList as $staff)
      <div class="id-card">
        {{-- Header --}}
        <div class="card-header">
          <div class="brand">
            <div class="brand-icon">
              <svg fill="none" stroke="#111" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
              </svg>
            </div>
            <div class="brand-name">IRONFORGE</div>
          </div>
          <span class="role-badge role-{{ strtolower($staff->role) }}">{{ $staff->role }}</span>
        </div>

        {{-- Body --}}
        <div class="card-body">
          <div class="user-info">
            <div class="user-name">{{ $staff->name }}</div>
            <div class="user-id">STAFF ID: {{ str_pad($staff->user_id, 4, '0', STR_PAD_LEFT) }}</div>
          </div>

          {{-- QR Code Section --}}
          <div class="qr-section">
            @if($staff->qr_code_path)
                <img src="{{ asset('storage/' . $staff->qr_code_path) }}" alt="QR Code">
            @else
                <div class="no-qr">No QR Generated</div>
            @endif
            <div class="qr-id">{{ $staff->qr_token }}</div>
          </div>
        </div>

        {{-- Footer --}}
        <div class="card-footer">
          <span class="footer-text">IRONFORGE STAFF</span>
          <div class="footer-dot"></div>
          <span class="footer-text">{{ now()->format('Y') }}</span>
        </div>
      </div>
    @endforeach
  </div>

</body>
</html>
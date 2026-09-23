<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
  <title>Waiting for Approval – APEX</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:opsz@14..32&display=swap" rel="stylesheet">
  <style>
    /* ── reset & base ── */
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
      background: #0f0f0f;
      color: #f0f0f0;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px 16px;
    }
    .wrapper {
      max-width: 520px;
      width: 100%;
      margin: 0 auto;
    }
    :root {
      --accent: #c8ff00;
      --surface: #1a1a1a;
      --border: #2e2e2e;
      --muted: #888;
      --radius: 14px;
    }
    .card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 24px 20px;
    }
    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: var(--accent);
      color: #111;
      font-weight: 700;
      font-size: 1rem;
      padding: 14px 24px;
      border-radius: 12px;
      border: none;
      text-decoration: none;
      transition: background 0.2s, transform 0.1s;
      cursor: pointer;
      width: 100%;
    }
    .btn:hover { background: #b8e600; transform: scale(1.01); }
    .btn-primary { background: var(--accent); color: #111; }

    /* ── status icon ── */
    .status-icon-wrap {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 28px;
    }
    .status-pulse {
      animation: pulse 2s infinite;
      background: rgba(200,255,0,0.06);
      border: 2px solid rgba(200,255,0,0.25);
    }
    .status-rejected {
      background: rgba(248,113,113,0.08);
      border: 2px solid rgba(248,113,113,0.3);
    }
    @keyframes pulse {
      0%, 100% { box-shadow: 0 0 0 0 rgba(200,255,0,0.2); }
      50%       { box-shadow: 0 0 0 14px rgba(200,255,0,0); }
    }

    /* ── badge ── */
    .badge {
      display: inline-block;
      padding: 8px 20px;
      border-radius: 30px;
      font-size: 0.7rem;
      font-weight: 700;
      letter-spacing: 1px;
      text-transform: uppercase;
    }
    .badge-pending {
      background: rgba(200,255,0,0.07);
      color: var(--accent);
      border: 1px solid rgba(200,255,0,0.25);
    }
    .badge-rejected {
      background: rgba(248,113,113,0.08);
      color: #f87171;
      border: 1px solid rgba(248,113,113,0.25);
    }

    /* ── detail rows ── */
    .detail-row {
      display: flex;
      justify-content: space-between;
      font-size: 0.85rem;
      padding: 8px 0;
      border-bottom: 1px solid rgba(255,255,255,0.04);
    }
    .detail-row:last-of-type { border-bottom: none; }
    .detail-label { color: var(--muted); }
    .detail-value { font-weight: 600; color: #fff; }
    .detail-value.accent { color: var(--accent); }

    .polling-status {
      font-size: 0.7rem;
      color: var(--muted);
      margin-top: 10px;
      text-align: center;
    }

    /* ── responsive fine-tune ── */
    @media (max-width: 420px) {
      .wrapper { padding: 0 4px; }
      .card { padding: 18px 14px; }
      .status-icon-wrap { width: 68px; height: 68px; }
      .status-icon-wrap svg { width: 30px; height: 30px; }
      h1 { font-size: 1.3rem; }
      .badge { font-size: 0.6rem; padding: 6px 16px; }
    }
  </style>
</head>
<body>
<div class="wrapper">

  <!-- ─── STATUS ICON ─── -->
  <div style="text-align:center;">
    <!-- pending state (default) -->
    <div id="status-icon-wrap" class="status-icon-wrap status-pulse">
      <svg width="36" height="36" fill="none" stroke="#c8ff00" stroke-width="1.5" viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="10"/>
        <polyline points="12 6 12 12 16 14"/>
      </svg>
    </div>
  </div>

  <!-- ─── TITLE ─── -->
  <h1 id="status-title" style="font-size:1.6rem; font-weight:800; text-align:center; margin-bottom:10px; letter-spacing:-0.02em;">
    Waiting for Coach Approval
  </h1>

  <!-- ─── MESSAGE ─── -->
  <p id="status-message" style="color:var(--muted); font-size:0.95rem; line-height:1.6; text-align:center; max-width:400px; margin:0 auto 28px;">
    Your subscription request has been sent to your coach for approval. This page will automatically update once they respond.
  </p>

  <!-- ─── BADGE ─── -->
  <div style="text-align:center; margin-bottom:28px;">
    <span id="status-badge" class="badge badge-pending">⏳ PENDING APPROVAL</span>
  </div>

  <!-- ─── SUBSCRIPTION DETAILS ─── -->
  <div class="card" style="margin-bottom:24px;">
    <div style="font-size:0.65rem; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:2px; margin-bottom:16px;">
      Your Subscription Details
    </div>
    <div>
      <div class="detail-row"><span class="detail-label">Plan</span><span class="detail-value" id="detail-plan">Calisthenics</span></div>
      <div class="detail-row"><span class="detail-label">Duration</span><span class="detail-value" id="detail-duration">Monthly</span></div>
      <div class="detail-row"><span class="detail-label">Coach Fee</span><span class="detail-value" id="detail-coach-fee">₱300 / Month</span></div>
      <div class="detail-row"><span class="detail-label">Total Fee</span><span class="detail-value accent" id="detail-total">₱1,100.00</span></div>
    </div>
  </div>

  <!-- ─── ACTION BUTTON ─── -->
  <div id="action-area">
    <!-- pending state: show polling status + hidden 'choose again' -->
    <div id="pending-actions">
      <div class="polling-status" id="polling-status">⏳ Checking for updates...</div>
      <!-- hidden 'choose again' for rejected state (will be shown via JS if needed) -->
      <a href="#" id="btn-choose-again" class="btn btn-primary" style="display:none; margin-top:12px;">
        Choose Again
      </a>
    </div>
    <!-- rejected state button (hidden by default) -->
    <div id="rejected-actions" style="display:none;">
      <a href="#" class="btn btn-primary">Choose Again</a>
    </div>
  </div>

  <!-- tiny spacer -->
  <div style="height:4px;"></div>
</div>

<script>
  (function() {
    // Simulate member data (same as blade variables)
    // In real usage, these would come from the backend.
    // We'll set them to 'pending' state by default, but we also allow switching for demo.
    let member = {
      coach_status: 'pending',   // 'pending' | 'approved' | 'rejected'
      fitness_plan: 'Calisthenics',
      membership_type: 'Monthly',
      coach_membership_type: '₱300 / Month',
      fee: 1100.00,
      // for demo: we can simulate rejection/approval via console
    };

    // DOM refs
    const iconWrap = document.getElementById('status-icon-wrap');
    const statusTitle = document.getElementById('status-title');
    const statusMsg = document.getElementById('status-message');
    const statusBadge = document.getElementById('status-badge');
    const detailPlan = document.getElementById('detail-plan');
    const detailDuration = document.getElementById('detail-duration');
    const detailCoachFee = document.getElementById('detail-coach-fee');
    const detailTotal = document.getElementById('detail-total');
    const pollingStatus = document.getElementById('polling-status');
    const pendingActions = document.getElementById('pending-actions');
    const rejectedActions = document.getElementById('rejected-actions');
    const btnChooseAgain = document.getElementById('btn-choose-again');

    // ── render UI based on member.coach_status ──
    function renderUI() {
      const status = member.coach_status;

      // reset classes
      iconWrap.className = 'status-icon-wrap';
      if (status === 'rejected') {
        iconWrap.classList.add('status-rejected');
      } else {
        iconWrap.classList.add('status-pulse');
      }

      // icon content
      if (status === 'rejected') {
        iconWrap.innerHTML = `
          <svg width="36" height="36" fill="none" stroke="#f87171" stroke-width="2" viewBox="0 0 24 24">
            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
          </svg>
        `;
      } else {
        iconWrap.innerHTML = `
          <svg width="36" height="36" fill="none" stroke="#c8ff00" stroke-width="1.5" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10"/>
            <polyline points="12 6 12 12 16 14"/>
          </svg>
        `;
      }

      // title & message
      if (status === 'rejected') {
        statusTitle.textContent = 'Request Rejected';
        statusMsg.textContent = 'Your coach request was rejected. You can go back and choose a different coach or train independently.';
        statusBadge.textContent = '✕ REJECTED';
        statusBadge.className = 'badge badge-rejected';
      } else if (status === 'approved') {
        statusTitle.textContent = '🎉 Approved!';
        statusMsg.textContent = 'Your coach has approved your request. Redirecting to dashboard...';
        statusBadge.textContent = '✅ APPROVED';
        statusBadge.className = 'badge badge-pending'; // reuse style
        statusBadge.style.color = '#4ade80';
        statusBadge.style.borderColor = 'rgba(74,222,128,0.3)';
        statusBadge.style.background = 'rgba(74,222,128,0.08)';
      } else {
        statusTitle.textContent = 'Waiting for Coach Approval';
        statusMsg.textContent = 'Your subscription request has been sent to your coach for approval. This page will automatically update once they respond.';
        statusBadge.textContent = '⏳ PENDING APPROVAL';
        statusBadge.className = 'badge badge-pending';
      }

      // details
      detailPlan.textContent = member.fitness_plan || '—';
      detailDuration.textContent = member.membership_type || '—';
      detailCoachFee.textContent = member.coach_membership_type || '—';
      detailTotal.textContent = '₱' + (member.fee ? member.fee.toFixed(2) : '0.00');

      // actions
      if (status === 'rejected') {
        pendingActions.style.display = 'none';
        rejectedActions.style.display = 'block';
        // hide polling status
        if (pollingStatus) pollingStatus.textContent = '';
      } else if (status === 'approved') {
        pendingActions.style.display = 'block';
        rejectedActions.style.display = 'none';
        if (pollingStatus) pollingStatus.textContent = '✅ Approved – redirecting...';
        // simulate redirect after 2s
        setTimeout(() => {
          window.location.href = '#dashboard'; // placeholder
        }, 2000);
      } else {
        pendingActions.style.display = 'block';
        rejectedActions.style.display = 'none';
        if (pollingStatus) pollingStatus.textContent = '⏳ Checking for updates...';
      }
    }

    // ── simulate polling (like the blade's setInterval) ──
    let checkCount = 0;
    function pollStatus() {
      // In a real app, fetch from route('member.coach.status')
      // Here we simulate: after 3 checks, auto-approve for demo.
      // But we also allow manual override via window.__setStatus
      checkCount++;
      if (pollingStatus) {
        pollingStatus.textContent = `Last checked: just now (check #${checkCount})`;
      }

      // For demo: if status is pending and checkCount > 8, auto-approve (just to show flow)
      // but we keep it pending unless user triggers via console.
      // We also listen for external changes via window.__setCoachStatus
    }

    // Expose a helper to change status from console (for demo)
    window.__setCoachStatus = function(newStatus) {
      if (['pending', 'approved', 'rejected'].includes(newStatus)) {
        member.coach_status = newStatus;
        renderUI();
        // if approved, simulate redirect after a moment
        if (newStatus === 'approved') {
          setTimeout(() => {
            window.location.href = '#dashboard-approved';
          }, 2000);
        }
      } else {
        console.warn('Status must be "pending", "approved", or "rejected"');
      }
    };

    // For rejected demo: we also need to show the "Choose Again" button
    // and link it. We'll set a dummy link.
    document.querySelectorAll('#rejected-actions .btn, #btn-choose-again').forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        alert('Navigate to plan selection (simulated)');
        // window.location.href = "{{ route('member.select-plan') }}";
      });
    });

    // ── start polling ──
    renderUI();

    // Simulate polling every 5s (like blade)
    setInterval(pollStatus, 5000);
    // initial check
    pollStatus();

    // Also allow click on "Choose Again" in pending (hidden by default, but if shown)
    // we handle it above.

    console.log('💡 To test different states, use: __setCoachStatus("approved") or __setCoachStatus("rejected")');
    console.log('💡 Current status:', member.coach_status);

    // ── optional: if you want to auto-reject after 15s for demo, uncomment:
    // setTimeout(() => { __setCoachStatus('rejected'); }, 15000);
  })();
</script>

<!-- for demonstration, we also handle the case where member is rejected from blade -->
<!-- this code is fully self-contained and responsive -->
</body>
</html>
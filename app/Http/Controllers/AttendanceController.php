<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Member;
use App\Models\User;
use App\Models\UserQrToken;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    // ── QR Scanner Page ──────────────────────────────────────────────────────
    public function scan()
    {
        $today     = now()->toDateString();

        $todayLogs = Attendance::with(['member.user', 'user'])
                        ->where('date', $today)
                        ->latest('id')
                        ->take(30)
                        ->get();

        $insideNow  = Attendance::where('date', $today)
                        ->whereNull('time_out')
                        ->whereNotNull('member_id')
                        ->count();

        $allMembers = Member::where('status', '!=', 'Suspended')
                        ->orderBy('name')
                        ->get(['id', 'name', 'membership_type', 'status']);

        $allStaff = User::whereIn('role', ['admin', 'staff', 'instructor'])
                        ->orderBy('name')
                        ->get(['id', 'name', 'role']);

        return view('attendance.scan', compact('todayLogs', 'insideNow', 'allMembers', 'allStaff'));
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    //  Live Polling Endpoint (AJAX POST)
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function live(Request $request)
    {
        $knownKeys = $request->input('known_keys', []);
        $today     = now()->toDateString();

        $logs = Attendance::with(['member.user', 'user'])
            ->where('date', $today)
            ->latest('id')
            ->get();

        $newRows = [];

        foreach ($logs as $log) {
            $isStaff = !$log->member_id;
            $dataKey = $isStaff
                ? 'staff-' . $log->staff_user_id
                : (string) $log->member_id;

            if (in_array($dataKey, $knownKeys)) {
                continue;
            }

            $personName  = $log->member?->name ?? $log->user?->name ?? 'Staff';
            $personRole  = $log->member?->membership_type ?? ucfirst($log->user?->role ?? 'Staff');
            $personPhoto = $log->member?->user?->photo
                        ?? $log->member?->photo
                        ?? $log->user?->photo;

            if ($personPhoto) {
                $avatarHtml = '<img src="' . asset('storage/' . $personPhoto) . '" '
                            . 'style="width:34px;height:34px;border-radius:50%;object-fit:cover;border:1px solid var(--border);">';
            } else {
                $bg     = $isStaff ? 'rgba(96,165,250,0.08)'  : 'rgba(200,255,0,0.08)';
                $border = $isStaff ? 'rgba(96,165,250,0.15)'  : 'rgba(200,255,0,0.15)';
                $color  = $isStaff ? '#60a5fa'                : 'var(--accent)';
                $initial = strtoupper(substr($personName, 0, 1));
                $avatarHtml = "<div style=\"width:34px;height:34px;border-radius:50%;"
                            . "background:{$bg};border:1px solid {$border};"
                            . "display:flex;align-items:center;justify-content:center;"
                            . "font-size:12px;font-weight:700;color:{$color};\">"
                            . "{$initial}</div>";
            }

            $methodBadge = $log->entry_method === 'manual'
                ? '<span style="display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:700;background:rgba(251,191,36,0.15);color:#fbbf24;">Manual</span>'
                : '<span style="display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:700;background:var(--surface2);color:var(--muted);">QR Scan</span>';

            $timeOutHtml = $log->time_out
                ? '<span style="font-size:13px;">' . $log->time_out->format('h:i A') . '</span>'
                : '<span style="display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:700;background:rgba(74,222,128,0.15);color:#4ade80;">Inside</span>';

            $statusBadge = $log->time_out
                ? '<span style="display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:700;background:var(--surface2);color:var(--muted);">Done</span>'
                : '<span style="display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:700;background:rgba(74,222,128,0.15);color:#4ade80;">Inside</span>';

            $timeIn            = $log->time_in?->format('h:i A') ?? '—';
            $durationFormatted = $log->duration_formatted ?? '—';

            $newRows[] = [
                'key'  => $dataKey,
                'html' => "
                    <td style='padding:12px 16px;'>{$avatarHtml}</td>
                    <td style='padding:12px 16px;'>
                        <div style='font-size:13px;font-weight:600;'>{$personName}</div>
                        <div style='font-size:11px;color:var(--muted);'>{$personRole}</div>
                    </td>
                    <td style='padding:12px 16px;font-size:13px;'>{$timeIn}</td>
                    <td style='padding:12px 16px;'>{$timeOutHtml}</td>
                    <td style='padding:12px 16px;font-size:13px;color:var(--muted);'>{$durationFormatted}</td>
                    <td style='padding:12px 16px;'>{$methodBadge}</td>
                    <td style='padding:12px 16px;'>{$statusBadge}</td>
                ",
            ];
        }

        $insideCount = Attendance::where('date', $today)
            ->whereNull('time_out')
            ->whereNotNull('member_id')
            ->count();

        $todayTotal = Attendance::where('date', $today)->count();

        return response()->json([
            'rows'         => $newRows,
            'inside_count' => $insideCount,
            'today_total'  => $todayTotal,
        ]);
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    //  Process QR Scan (AJAX POST)
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function processQr(Request $request)
    {
        try {
            $raw   = trim($request->input('qr_data', ''));
            $today = now()->toDateString();
            $now   = now();

            $member    = null;
            $staffUser = null;

            // FORMAT 1 — Member QR  (IFG-MEM-000001)
            if (preg_match('/^IFG-MEM-(\d+)$/i', $raw, $parts)) {
                $qrId   = strtoupper($raw);
                $member = Member::where('qr_id', $qrId)->first();

                if (!$member) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Member QR code not recognised. Re-generate QR and try again.',
                        'status'  => 'invalid',
                    ]);
                }
            }

            // FORMAT 2 — Staff / Admin / Instructor QR  (IRONFORGE|ROLE|user_id|token)
            elseif (preg_match('/^IRONFORGE\|([A-Z]+)\|(\d+)\|([A-Z0-9\-]+)$/i', $raw, $parts)) {
                $type  = strtoupper($parts[1]);
                $id    = (int) $parts[2];
                $token = $parts[3];

                if ($type === 'MBR') {
                    $member = Member::where('id', $id)->where('qr_token', $token)->first();
                    if (!$member) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Invalid or tampered member QR code.',
                            'status'  => 'invalid',
                        ]);
                    }
                } else {
                    $qrRecord = UserQrToken::with('user')
                                    ->where('user_id', $id)
                                    ->where('qr_token', $token)
                                    ->first();

                    if (!$qrRecord) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Invalid staff QR code.',
                            'status'  => 'invalid',
                        ]);
                    }
                    $staffUser = $qrRecord;
                }
            }

            // FORMAT 3 — Legacy numeric fallback
            elseif (preg_match('/^ID:\s*(\d+)$/i', $raw, $m)) {
                $member = Member::find((int) $m[1]);
                if (!$member) {
                    return response()->json(['success' => false, 'message' => 'Member not found.', 'status' => 'invalid']);
                }
            }
            elseif (ctype_digit($raw)) {
                $member = Member::find((int) $raw);
                if (!$member) {
                    return response()->json(['success' => false, 'message' => 'No member found with that ID.', 'status' => 'invalid']);
                }
            }
            else {
                return response()->json([
                    'success' => false,
                    'message' => 'Unrecognised QR format. Please re-generate the QR code.',
                    'status'  => 'invalid',
                ]);
            }

            // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
            //  MEMBER ATTENDANCE LOGIC
            // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
            if ($member) {
                if ($member->status === 'Suspended') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Membership SUSPENDED. Entry denied.',
                        'member'  => $member->name,
                        'status'  => 'suspended',
                    ]);
                }
                if ($member->status === 'Expired') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Membership EXPIRED. Please renew first.',
                        'member'  => $member->name,
                        'status'  => 'expired',
                    ]);
                }

                $member->loadMissing('user');
                $photoUrl = null;
                if ($member->user && $member->user->photo) {
                    $photoUrl = asset('storage/' . $member->user->photo);
                } elseif ($member->photo) {
                    $photoUrl = asset('storage/' . $member->photo);
                }

                $open = Attendance::where('member_id', $member->id)
                            ->where('date', $today)
                            ->whereNull('time_out')
                            ->latest('id')
                            ->first();

                if ($open) {
                    // ── TIME OUT ─────────────────────────────────────────────
                    // FIX: use $open->time_in->diffInMinutes($now) — time_in is
                    // already a Carbon instance (cast in the model), so no
                    // Carbon::parse() needed. diffInMinutes($now) gives the
                    // correct elapsed minutes from time_in TO now.
                    $duration = max(1, (int) round($open->time_in->diffInMinutes($now)));

                    $open->update([
                        'time_out'         => $now,
                        'duration_minutes' => $duration,
                        'entry_method'     => 'qr_scan',
                    ]);

                    $h    = intdiv($duration, 60);
                    $mn   = $duration % 60;
                    $dStr = $h > 0 ? "{$h}h {$mn}m" : "{$mn}m";

                    return response()->json([
                        'success'    => true,
                        'action'     => 'timeout',
                        'member_id'  => $member->id,
                        'member'     => $member->name,
                        'photo'      => $photoUrl,
                        'membership' => $member->membership_type,
                        'status'     => $member->status,
                        'time_in'    => $open->time_in->format('h:i A'),
                        'time_out'   => $now->format('h:i A'),
                        'duration'   => $dStr,
                        'message'    => "Time Out recorded! Duration: {$dStr}",
                    ]);
                } else {
                    // ── TIME IN ──────────────────────────────────────────────
                    Attendance::create([
                        'member_id'    => $member->id,
                        'time_in'      => $now,
                        'date'         => $today,
                        'entry_method' => 'qr_scan',
                    ]);

                    return response()->json([
                        'success'    => true,
                        'action'     => 'timein',
                        'member_id'  => $member->id,
                        'member'     => $member->name,
                        'photo'      => $photoUrl,
                        'membership' => $member->membership_type,
                        'status'     => $member->status,
                        'time_in'    => $now->format('h:i A'),
                        'end_date'   => $member->end_date
                                            ? Carbon::parse($member->end_date)->format('M d, Y')
                                            : '—',
                        'message'    => 'Time In recorded! Welcome to IRONFORGE!',
                    ]);
                }
            }

            // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
            //  STAFF / ADMIN / INSTRUCTOR ATTENDANCE LOGIC
            // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
            if ($staffUser) {
                $staffPhotoUrl = null;
                if ($staffUser->user && $staffUser->user->photo) {
                    $staffPhotoUrl = asset('storage/' . $staffUser->user->photo);
                }

                $open = Attendance::where('staff_user_id', $staffUser->user_id)
                            ->where('date', $today)
                            ->whereNull('time_out')
                            ->latest('id')
                            ->first();

                if ($open) {
                    // ── TIME OUT ─────────────────────────────────────────────
                    // FIX: same fix — $open->time_in is already Carbon, call
                    // diffInMinutes($now) directly on it.
                    $duration = max(1, (int) round($open->time_in->diffInMinutes($now)));

                    $open->update([
                        'time_out'         => $now,
                        'duration_minutes' => $duration,
                    ]);

                    $h    = intdiv($duration, 60);
                    $mn   = $duration % 60;
                    $dStr = $h > 0 ? "{$h}h {$mn}m" : "{$mn}m";

                    return response()->json([
                        'success'    => true,
                        'action'     => 'timeout',
                        'member_id'  => 'staff-' . $staffUser->user_id,
                        'is_staff'   => true,
                        'member'     => $staffUser->name,
                        'photo'      => $staffPhotoUrl,
                        'membership' => ucfirst($staffUser->role),
                        'status'     => 'active',
                        'time_in'    => $open->time_in->format('h:i A'),
                        'time_out'   => $now->format('h:i A'),
                        'duration'   => $dStr,
                        'message'    => "Time Out recorded! Duration: {$dStr}",
                    ]);
                } else {
                    // ── TIME IN ──────────────────────────────────────────────
                    Attendance::create([
                        'member_id'     => null,
                        'staff_user_id' => $staffUser->user_id,
                        'time_in'       => $now,
                        'date'          => $today,
                        'scanned_by'    => $staffUser->qr_token,
                        'entry_method'  => 'qr_scan',
                    ]);

                    return response()->json([
                        'success'    => true,
                        'action'     => 'timein',
                        'member_id'  => 'staff-' . $staffUser->user_id,
                        'is_staff'   => true,
                        'member'     => $staffUser->name,
                        'photo'      => $staffPhotoUrl,
                        'membership' => ucfirst($staffUser->role),
                        'status'     => 'active',
                        'time_in'    => $now->format('h:i A'),
                        'end_date'   => '—',
                        'message'    => 'Staff Time In recorded! Welcome, ' . $staffUser->name . '!',
                    ]);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Could not process QR. Try again.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server Error: ' . $e->getMessage(),
            ]);
        }
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    //  Manual Entry (AJAX POST)
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function manualEntry(Request $request)
    {
        try {
            $rawId  = $request->input('manual_member_id');
            $action = $request->input('manual_action', 'timein');
            $today  = now()->toDateString();
            $now    = now();

            // ── Staff / Admin / Instructor ────────────────────────────────────
            if (str_starts_with((string) $rawId, 'staff-')) {
                $userId = (int) str_replace('staff-', '', $rawId);
                $user   = User::find($userId);

                if (!$user) {
                    return response()->json(['success' => false, 'message' => 'Staff not found.']);
                }

                $staffPhotoUrl = $user->photo ? asset('storage/' . $user->photo) : null;

                if ($action === 'timeout') {
                    $open = Attendance::where('staff_user_id', $userId)
                                ->where('date', $today)
                                ->whereNull('time_out')
                                ->latest('id')
                                ->first();

                    if ($open) {
                        // FIX: $open->time_in is already Carbon — call diffInMinutes($now) directly
                        $dur  = max(1, (int) round($open->time_in->diffInMinutes($now)));
                        $open->update(['time_out' => $now, 'duration_minutes' => $dur, 'entry_method' => 'manual']);

                        $h    = intdiv($dur, 60);
                        $mn   = $dur % 60;
                        $dStr = $h > 0 ? "{$h}h {$mn}m" : "{$mn}m";

                        return response()->json([
                            'success'    => true,
                            'message'    => 'Staff Time Out recorded.',
                            'member_id'  => $rawId,
                            'is_staff'   => true,
                            'member'     => $user->name,
                            'membership' => ucfirst($user->role),
                            'photo'      => $staffPhotoUrl,
                            'time_out'   => $now->format('h:i A'),
                            'duration'   => $dStr,
                        ]);
                    }
                    return response()->json(['success' => false, 'message' => 'No active staff Time In found.']);
                }

                Attendance::create([
                    'staff_user_id' => $userId,
                    'time_in'       => $now,
                    'date'          => $today,
                    'entry_method'  => 'manual',
                ]);

                return response()->json([
                    'success'      => true,
                    'message'      => 'Staff Time In recorded.',
                    'member_id'    => $rawId,
                    'is_staff'     => true,
                    'member'       => $user->name,
                    'membership'   => ucfirst($user->role),
                    'photo'        => $staffPhotoUrl,
                    'time_in'      => $now->format('h:i A'),
                    'entry_method' => 'manual',
                ]);
            }

            // ── Member ────────────────────────────────────────────────────────
            $mid    = (int) $rawId;
            $member = Member::with('user')->find($mid);

            if (!$member) {
                return response()->json(['success' => false, 'message' => 'Member not found.']);
            }

            $photoUrl = null;
            if ($member->user && $member->user->photo) {
                $photoUrl = asset('storage/' . $member->user->photo);
            } elseif ($member->photo) {
                $photoUrl = asset('storage/' . $member->photo);
            }

            if ($action === 'timeout') {
                $open = Attendance::where('member_id', $mid)
                            ->where('date', $today)
                            ->whereNull('time_out')
                            ->latest('id')
                            ->first();

                if ($open) {
                    // FIX: $open->time_in is already Carbon — call diffInMinutes($now) directly
                    $dur  = max(1, (int) round($open->time_in->diffInMinutes($now)));
                    $open->update(['time_out' => $now, 'duration_minutes' => $dur, 'entry_method' => 'manual']);

                    $h    = intdiv($dur, 60);
                    $mn   = $dur % 60;
                    $dStr = $h > 0 ? "{$h}h {$mn}m" : "{$mn}m";

                    return response()->json([
                        'success'    => true,
                        'message'    => 'Manual Time Out recorded.',
                        'member_id'  => $rawId,
                        'member'     => $member->name,
                        'membership' => $member->membership_type,
                        'photo'      => $photoUrl,
                        'time_out'   => $now->format('h:i A'),
                        'duration'   => $dStr,
                    ]);
                }
                return response()->json(['success' => false, 'message' => 'No open Time In found for today.']);
            }

            Attendance::create([
                'member_id'    => $mid,
                'time_in'      => $now,
                'date'         => $today,
                'entry_method' => 'manual',
            ]);

            return response()->json([
                'success'      => true,
                'message'      => 'Manual Time In recorded.',
                'member_id'    => $rawId,
                'member'       => $member->name,
                'membership'   => $member->membership_type,
                'photo'        => $photoUrl,
                'time_in'      => $now->format('h:i A'),
                'entry_method' => 'manual',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Database Error: ' . $e->getMessage(),
            ]);
        }
    }

    // ── Attendance Log Page ───────────────────────────────────────────────────
    public function index(Request $request)
    {
        $filterDate   = $request->get('date', now()->toDateString());
        $filterMember = $request->get('member', '');
        $filterStatus = $request->get('status', '');
        $filterMethod = $request->get('method', '');
        $filterRole   = $request->get('role', '');

        $query = Attendance::with(['member.user', 'user'])->where('date', $filterDate);

        if ($filterMember) {
            $query->whereHas('member', fn($q) => $q->where('name', 'like', "%{$filterMember}%"));
        }
        if ($filterStatus === 'inside') {
            $query->whereNull('time_out')->whereNotNull('member_id');
        } elseif ($filterStatus === 'done') {
            $query->whereNotNull('time_out');
        }
        if ($filterMethod === 'qr_scan') {
            $query->where('entry_method', 'qr_scan');
        } elseif ($filterMethod === 'manual') {
            $query->where('entry_method', 'manual');
        }
        if ($filterRole === 'member') {
            $query->whereNotNull('member_id');
        } elseif (in_array($filterRole, ['staff', 'instructor', 'admin'])) {
            $userTokens = UserQrToken::where('role', $filterRole)->pluck('user_id');
            $query->whereNull('member_id')->whereIn('staff_user_id', $userTokens);
        }

        $logs = $query->latest('id')->paginate(15)->withQueryString();

        $stats = Attendance::where('date', $filterDate)
                    ->selectRaw('
                        COUNT(*) as total_visits,
                        SUM(CASE WHEN time_out IS NULL AND member_id IS NOT NULL THEN 1 ELSE 0 END) as inside_now,
                        SUM(CASE WHEN time_out IS NOT NULL THEN 1 ELSE 0 END) as completed,
                        ROUND(AVG(duration_minutes)) as avg_duration,
                        SUM(CASE WHEN entry_method = "manual" THEN 1 ELSE 0 END) as manual_count,
                        SUM(CASE WHEN entry_method = "qr_scan" OR entry_method IS NULL THEN 1 ELSE 0 END) as qr_count
                    ')
                    ->first();

        $allMembers = Member::orderBy('name')->get(['id', 'name', 'membership_type']);

        return view('attendance.index', compact(
            'logs', 'stats', 'allMembers',
            'filterDate', 'filterMember', 'filterStatus', 'filterMethod', 'filterRole'
        ));
    }

    // ── Manual Time Out from log page ────────────────────────────────────────
    public function timeOut(Request $request)
    {
        $record = Attendance::findOrFail($request->input('timeout_id'));
        $now    = now();
        // FIX: $record->time_in is already Carbon — no Carbon::parse() needed
        $dur    = max(1, (int) round($record->time_in->diffInMinutes($now)));
        $record->update(['time_out' => $now, 'duration_minutes' => $dur]);

        return back()->with('success', 'Time Out recorded.');
    }

    // ── Delete attendance record ─────────────────────────────────────────────
    public function destroy(Request $request)
    {
        Attendance::findOrFail($request->input('delete_id'))->delete();
        return back()->with('success', 'Record deleted.');
    }

    // ── Add manual entry from log page ───────────────────────────────────────
    public function addManual(Request $request)
    {
        $mid  = (int) $request->input('manual_member_id');
        $date = now()->toDateString();
        $tin  = $date . ' ' . $request->input('manual_time_in', now()->format('H:i:s'));
        $tout = $request->filled('manual_time_out')
                    ? $date . ' ' . $request->input('manual_time_out')
                    : null;
        // FIX: use Carbon objects for accurate minute diff instead of strtotime arithmetic
        $dur  = $tout
                    ? max(1, (int) round(Carbon::parse($tin)->diffInMinutes(Carbon::parse($tout))))
                    : null;

        Attendance::create([
            'member_id'        => $mid,
            'time_in'          => $tin,
            'time_out'         => $tout,
            'duration_minutes' => $dur,
            'date'             => $date,
            'entry_method'     => 'manual',
        ]);

        return back()->with('success', 'Manual attendance entry added.');
    }

    // ── Generate QR Tokens (Admin only) ─────────────────────────────────────
    public function generateTokens()
    {
        $log = [];

        $members = Member::all();
        foreach ($members as $m) {
            Member::generateQrCode($m);
            $log[] = ['type' => 'success', 'text' => "Member [{$m->id}] {$m->name} → QR Generated (IFG-MEM-" . str_pad($m->id, 6, '0', STR_PAD_LEFT) . ")"];
        }

        $users = User::whereIn('role', ['admin', 'staff', 'instructor'])->get();
        foreach ($users as $u) {
            $record = UserQrToken::firstOrCreate(
                ['user_id' => $u->id],
                [
                    'role'     => $u->role,
                    'name'     => $u->name,
                    'qr_token' => strtoupper($u->role[0]) . 'SR-' . strtoupper(bin2hex(random_bytes(16))),
                ]
            );

            $record->update(['role' => $u->role, 'name' => $u->name]);
            UserQrToken::generateStaffQrCode($record);

            $log[] = ['type' => 'success', 'text' => "User [{$u->id}] {$u->name} ({$u->role}) → QR Generated/Updated"];
        }

        return view('attendance.generate-tokens', compact('log'));
    }

    // ── QR Code List Page ────────────────────────────────────────────────────
    public function qrList(Request $request)
    {
        $group  = in_array($request->get('group'), ['members', 'staff'])
                    ? $request->get('group')
                    : 'members';
        $search = $request->get('q', '');

        $members   = collect();
        $staffList = collect();

        if ($group === 'staff') {
            $staffList = UserQrToken::with('user')
                ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
                ->orderBy('role')
                ->orderBy('name')
                ->get();
        } else {
            $members = Member::whereNotNull('qr_token')
                ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
                ->orderBy('name')
                ->get();
        }

        return view('attendance.qr-list', compact('members', 'staffList', 'group', 'search'));
    }
}
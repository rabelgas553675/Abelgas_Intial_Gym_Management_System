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
        $todayLogs = Attendance::with('member')
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

        return view('attendance.scan', compact('todayLogs', 'insideNow', 'allMembers'));
    }

    // ── Process QR Scan (AJAX POST) ──────────────────────────────────────────
    public function processQr(Request $request)
    {
        $raw   = trim($request->input('qr_data', ''));
        $today = now()->toDateString();
        $now   = now();

        $member    = null;
        $staffUser = null;

        // New token format: IRONFORGE|TYPE|ID|TOKEN
        if (preg_match('/^IRONFORGE\|([A-Z]+)\|(\d+)\|([A-Z0-9\-]+)$/i', $raw, $parts)) {
            $type  = strtoupper($parts[1]);
            $id    = (int) $parts[2];
            $token = $parts[3];

            if ($type === 'MBR') {
                $member = Member::where('id', $id)->where('qr_token', $token)->first();
                if (!$member) {
                    return response()->json(['success' => false, 'message' => 'Invalid or tampered QR code.', 'status' => 'invalid']);
                }
            } else {
                $qrRecord = UserQrToken::with('user')
                                ->where('user_id', $id)
                                ->where('qr_token', $token)
                                ->first();
                if (!$qrRecord) {
                    return response()->json(['success' => false, 'message' => 'Invalid staff QR code.', 'status' => 'invalid']);
                }
                $staffUser = $qrRecord;
            }
        }
        // Legacy format: "ID: 3"
        elseif (preg_match('/ID:\s*(\d+)/i', $raw, $m)) {
            $member = Member::find((int) $m[1]);
            if (!$member) {
                return response()->json(['success' => false, 'message' => 'Member not found.', 'status' => 'invalid']);
            }
        }
        // Plain numeric ID
        elseif (ctype_digit($raw)) {
            $member = Member::find((int) $raw);
            if (!$member) {
                return response()->json(['success' => false, 'message' => 'No member found with that ID.', 'status' => 'invalid']);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'Unrecognized QR format.', 'status' => 'invalid']);
        }

        // ── Member attendance ─────────────────────────────────────────────────
        if ($member) {
            if ($member->status === 'Suspended') {
                return response()->json(['success' => false, 'message' => 'Membership SUSPENDED. Entry denied.', 'member' => $member->name, 'status' => 'suspended']);
            }
            if ($member->status === 'Expired') {
                return response()->json(['success' => false, 'message' => 'Membership EXPIRED. Please renew first.', 'member' => $member->name, 'status' => 'expired']);
            }

            $open = Attendance::where('member_id', $member->id)
                        ->where('date', $today)
                        ->whereNull('time_out')
                        ->latest('id')
                        ->first();

            if ($open) {
                // TIME OUT
                $duration = (int) round($now->diffInMinutes($open->time_in));
                $open->update(['time_out' => $now, 'duration_minutes' => $duration, 'entry_method' => 'qr_scan']);

                $h = intdiv($duration, 60); $mn = $duration % 60;
                $dStr = $h > 0 ? "{$h}h {$mn}m" : "{$mn}m";

                return response()->json([
                    'success'    => true, 'action' => 'timeout',
                    'member_id'  => $member->id,
                    'member'     => $member->name,
                    'photo'      => $member->photo ? asset('storage/' . $member->photo) : null,
                    'membership' => $member->membership_type,
                    'status'     => $member->status,
                    'time_in'    => Carbon::parse($open->time_in)->format('h:i A'),
                    'time_out'   => $now->format('h:i A'),
                    'duration'   => $dStr,
                    'message'    => "Time Out recorded! Duration: {$dStr}",
                ]);
            } else {
                // TIME IN
                Attendance::create([
                    'member_id'    => $member->id,
                    'time_in'      => $now,
                    'date'         => $today,
                    'entry_method' => 'qr_scan',
                ]);

                return response()->json([
                    'success'    => true, 'action' => 'timein',
                    'member_id'  => $member->id,
                    'member'     => $member->name,
                    'photo'      => $member->photo ? asset('storage/' . $member->photo) : null,
                    'membership' => $member->membership_type,
                    'status'     => $member->status,
                    'time_in'    => $now->format('h:i A'),
                    'end_date'   => $member->end_date ? Carbon::parse($member->end_date)->format('M d, Y') : '—',
                    'message'    => 'Time In recorded! Welcome to IRONFORGE!',
                ]);
            }
        }

        // ── Staff/Admin/Instructor attendance ────────────────────────────────
        if ($staffUser) {
            $open = Attendance::where('scanned_by', $staffUser->qr_token)
                        ->where('date', $today)
                        ->whereNull('time_out')
                        ->latest('id')
                        ->first();

            if ($open) {
                $duration = (int) round($now->diffInMinutes($open->time_in));
                $open->update(['time_out' => $now, 'duration_minutes' => $duration]);

                $h = intdiv($duration, 60); $mn = $duration % 60;
                $dStr = $h > 0 ? "{$h}h {$mn}m" : "{$mn}m";

                return response()->json([
                    'success'    => true, 'action' => 'timeout',
                    'member_id'  => 'staff_' . $staffUser->user_id,
                    'member'     => $staffUser->name,
                    'photo'      => null,
                    'membership' => ucfirst($staffUser->role),
                    'status'     => 'active',
                    'time_in'    => Carbon::parse($open->time_in)->format('h:i A'),
                    'time_out'   => $now->format('h:i A'),
                    'duration'   => $dStr,
                    'message'    => "Time Out recorded! Duration: {$dStr}",
                ]);
            } else {
                Attendance::create([
                    'member_id'    => null,
                    'time_in'      => $now,
                    'date'         => $today,
                    'scanned_by'   => $staffUser->qr_token,
                    'entry_method' => 'qr_scan',
                ]);

                return response()->json([
                    'success'    => true, 'action' => 'timein',
                    'member_id'  => 'staff_' . $staffUser->user_id,
                    'member'     => $staffUser->name,
                    'photo'      => null,
                    'membership' => ucfirst($staffUser->role),
                    'status'     => 'active',
                    'time_in'    => $now->format('h:i A'),
                    'end_date'   => '—',
                    'message'    => 'Staff Time In recorded! Welcome, ' . $staffUser->name . '!',
                ]);
            }
        }

        return response()->json(['success' => false, 'message' => 'Could not process QR. Try again.']);
    }

    // ── Manual Entry (AJAX POST) ─────────────────────────────────────────────
    public function manualEntry(Request $request)
    {
        $mid    = (int) $request->input('manual_member_id');
        $action = $request->input('manual_action', 'timein');
        $today  = now()->toDateString();
        $now    = now();

        $member = Member::find($mid);
        if (!$member) {
            return response()->json(['success' => false, 'message' => 'Member not found.']);
        }

        if ($action === 'timeout') {
            $open = Attendance::where('member_id', $mid)
                        ->where('date', $today)
                        ->whereNull('time_out')
                        ->latest('id')
                        ->first();

            if ($open) {
                $dur = (int) round($now->diffInMinutes($open->time_in));
                $open->update(['time_out' => $now, 'duration_minutes' => $dur, 'entry_method' => 'manual']);
                return response()->json(['success' => true, 'message' => 'Manual Time Out recorded.']);
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
            'success'    => true,
            'message'    => 'Manual Time In recorded for ' . $member->name . '.',
            'member'     => $member->name,
            'membership' => $member->membership_type,
            'time_in'    => $now->format('h:i A'),
        ]);
    }

    // ── Attendance Log Page ───────────────────────────────────────────────────
    public function index(Request $request)
    {
        $filterDate   = $request->get('date', now()->toDateString());
        $filterMember = $request->get('member', '');
        $filterStatus = $request->get('status', '');
        $filterMethod = $request->get('method', '');
        $filterRole   = $request->get('role', '');  // 👈 ADDED

        $query = Attendance::with('member')->where('date', $filterDate);

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

        // 👇 Role filter - ADDED
        if ($filterRole === 'member') {
            $query->whereNotNull('member_id');
        } elseif (in_array($filterRole, ['staff', 'instructor', 'admin'])) {
            // Staff/instructor/admin rows have no member_id, tracked by scanned_by token
            $userTokens = \App\Models\UserQrToken::where('role', $filterRole)->pluck('qr_token');
            $query->whereNull('member_id')->whereIn('scanned_by', $userTokens);
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
            'filterDate', 'filterMember', 'filterStatus', 'filterMethod', 'filterRole'  // 👈 ADDED filterRole
        ));
    }

    // ── Manual Time Out from log page ────────────────────────────────────────
    public function timeOut(Request $request)
    {
        $record = Attendance::findOrFail($request->input('timeout_id'));
        $now    = now();
        $dur    = (int) round($now->diffInMinutes($record->time_in));
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
        $tout = $request->filled('manual_time_out') ? $date . ' ' . $request->input('manual_time_out') : null;
        $dur  = $tout ? max(0, (int) round((strtotime($tout) - strtotime($tin)) / 60)) : null;

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

        // Members without tokens
        $members = Member::whereNull('qr_token')->get();
        foreach ($members as $m) {
            $token = 'MBR-' . strtoupper(bin2hex(random_bytes(16)));
            $m->update(['qr_token' => $token]);
            $log[] = ['type' => 'success', 'text' => "Member [{$m->id}] {$m->name} → {$token}"];
        }

        // Users (admin/staff/instructor) without tokens
        $users = User::whereIn('role', ['admin', 'staff', 'instructor'])->get();
        foreach ($users as $u) {
            $exists = UserQrToken::where('user_id', $u->id)->exists();
            if ($exists) {
                $log[] = ['type' => 'skip', 'text' => "User [{$u->id}] {$u->name} ({$u->role}) already has a token — skipped"];
                continue;
            }
            $prefix = strtoupper($u->role[0]);
            $token  = "{$prefix}SR-" . strtoupper(bin2hex(random_bytes(16)));
            UserQrToken::create([
                'user_id'  => $u->id,
                'role'     => $u->role,
                'name'     => $u->name,
                'qr_token' => $token,
            ]);
            $log[] = ['type' => 'success', 'text' => "User [{$u->id}] {$u->name} ({$u->role}) → {$token}"];
        }

        if (empty($log)) {
            $log[] = ['type' => 'info', 'text' => 'All members and users already have QR tokens. Nothing to do.'];
        }

        return view('attendance.generate-tokens', compact('log'));
    }

    // ── QR Code List Page ────────────────────────────────────────────────────
    public function qrList(Request $request)
    {
        $group  = $request->get('group', 'members');
        $search = $request->get('q', '');

        $members   = collect();
        $staffList = collect();

        if ($group === 'members') {
            $members = Member::whereNotNull('qr_token')
                ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
                ->orderBy('name')
                ->get();
        } else {
            $staffList = UserQrToken::with('user')
                ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
                ->orderBy('role')
                ->orderBy('name')
                ->get();
        }

        return view('attendance.qr-list', compact('members', 'staffList', 'group', 'search'));
    }
}
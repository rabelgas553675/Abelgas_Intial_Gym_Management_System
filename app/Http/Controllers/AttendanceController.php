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
        // FIXED: Eager load 'user' relationship to show staff names in the log
        $todayLogs = Attendance::with(['member', 'user'])
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

        // FIXED: Fetch staff, instructors, and admins for the manual dropdown
        $allStaff = User::whereIn('role', ['admin', 'staff', 'instructor'])
                        ->orderBy('name')
                        ->get(['id', 'name', 'role']);

        return view('attendance.scan', compact('todayLogs', 'insideNow', 'allMembers', 'allStaff'));
    }

    // ── Process QR Scan (AJAX POST) ──────────────────────────────────────────
    public function processQr(Request $request)
    {
        try {
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
                    $duration = max(1, (int) round($now->diffInMinutes(Carbon::parse($open->time_in))));
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
                $open = Attendance::where('staff_user_id', $staffUser->user_id)
                            ->where('date', $today)
                            ->whereNull('time_out')
                            ->latest('id')
                            ->first();

                if ($open) {
                    $duration = max(1, (int) round($now->diffInMinutes(Carbon::parse($open->time_in))));
                    $open->update(['time_out' => $now, 'duration_minutes' => $duration]);

                    $h = intdiv($duration, 60); $mn = $duration % 60;
                    $dStr = $h > 0 ? "{$h}h {$mn}m" : "{$mn}m";

                    return response()->json([
                        'success'    => true, 'action' => 'timeout',
                        'member_id'  => 'staff-' . $staffUser->user_id, // Standardized to staff-ID
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
                        'member_id'      => null,
                        'staff_user_id'  => $staffUser->user_id,
                        'time_in'        => $now,
                        'date'           => $today,
                        'scanned_by'     => $staffUser->qr_token,
                        'entry_method'   => 'qr_scan',
                    ]);

                    return response()->json([
                        'success'    => true, 'action' => 'timein',
                        'member_id'  => 'staff-' . $staffUser->user_id, // Standardized to staff-ID
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
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()]);
        }
    }

    // ── Manual Entry (AJAX POST) ─────────────────────────────────────────────
    public function manualEntry(Request $request)
    {
        try {
            $rawId  = $request->input('manual_member_id');
            $action = $request->input('manual_action', 'timein');
            $today  = now()->toDateString();
            $now    = now();

            // FIXED: Handle Staff Manual Entry
            if (str_starts_with($rawId, 'staff-')) {
                $userId = (int) str_replace('staff-', '', $rawId);
                $user   = User::find($userId);
                if (!$user) return response()->json(['success' => false, 'message' => 'Staff not found.']);

                if ($action === 'timeout') {
                    $open = Attendance::where('staff_user_id', $userId)
                                ->where('date', $today)
                                ->whereNull('time_out')
                                ->latest('id')
                                ->first();

                    if ($open) {
                        $dur = max(1, (int) round($now->diffInMinutes(Carbon::parse($open->time_in))));
                        $open->update(['time_out' => $now, 'duration_minutes' => $dur, 'entry_method' => 'manual']);
                        
                        $h = intdiv($dur, 60); $mn = $dur % 60;
                        $dStr = $h > 0 ? "{$h}h {$mn}m" : "{$mn}m";

                        return response()->json([
                            'success' => true, 
                            'message' => 'Staff Time Out recorded.', 
                            'member_id' => $rawId,
                            'time_out' => $now->format('h:i A'),
                            'duration' => $dStr
                        ]);
                    }
                    return response()->json(['success' => false, 'message' => 'No active staff Time In found.']);
                }

                Attendance::create(['staff_user_id' => $userId, 'time_in' => $now, 'date' => $today, 'entry_method' => 'manual']);
                return response()->json([
                    'success' => true, 
                    'message' => 'Staff Time In recorded.', 
                    'member_id' => $rawId,
                    'member' => $user->name, 
                    'membership' => ucfirst($user->role), 
                    'time_in' => $now->format('h:i A')
                ]);
            } 
            
            // Handle Member Manual Entry
            else {
                $mid = (int) $rawId;
                $member = Member::find($mid);
                if (!$member) return response()->json(['success' => false, 'message' => 'Member not found.']);

                if ($action === 'timeout') {
                    $open = Attendance::where('member_id', $mid)
                                ->where('date', $today)
                                ->whereNull('time_out')
                                ->latest('id')
                                ->first();

                    if ($open) {
                        $dur = max(1, (int) round($now->diffInMinutes(Carbon::parse($open->time_in))));
                        $open->update(['time_out' => $now, 'duration_minutes' => $dur, 'entry_method' => 'manual']);
                        
                        $h = intdiv($dur, 60); $mn = $dur % 60;
                        $dStr = $h > 0 ? "{$h}h {$mn}m" : "{$mn}m";

                        return response()->json([
                            'success' => true, 
                            'message' => 'Manual Time Out recorded.',
                            'member_id' => $rawId,
                            'time_out' => $now->format('h:i A'),
                            'duration' => $dStr
                        ]);
                    }
                    return response()->json(['success' => false, 'message' => 'No open Time In found for today.']);
                }

                Attendance::create(['member_id' => $mid, 'time_in' => $now, 'date' => $today, 'entry_method' => 'manual']);
                return response()->json([
                    'success' => true, 
                    'message' => 'Manual Time In recorded.', 
                    'member_id' => $rawId,
                    'member' => $member->name, 
                    'membership' => $member->membership_type, 
                    'time_in' => $now->format('h:i A')
                ]);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Database Error: ' . $e->getMessage()]);
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

        // FIXED: Eager load 'user' relationship
       $query = Attendance::with(['member', 'user'])->where('date', $filterDate);

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
            $userTokens = \App\Models\UserQrToken::where('role', $filterRole)->pluck('user_id');
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

    // ── Manual Time Out from log page (FIXED) ────────────────────────
    public function timeOut(Request $request)
    {
        $record = Attendance::findOrFail($request->input('timeout_id'));
        $now    = now();
        $dur    = max(1, (int) round($now->diffInMinutes(Carbon::parse($record->time_in))));
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
        $dur  = $tout ? max(1, (int) round((strtotime($tout) - strtotime($tin)) / 60)) : null;

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
            $log[] = ['type' => 'success', 'text' => "Member [{$m->id}] {$m->name} → QR Generated"];
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

            $record->update([
                'role' => $u->role,
                'name' => $u->name,
            ]);

            UserQrToken::generateStaffQrCode($record);

            $log[] = ['type' => 'success', 'text' => "User [{$u->id}] {$u->name} ({$u->role}) → QR Generated/Updated"];
        }

        return view('attendance.generate-tokens', compact('log'));
    }

    // ── QR Code List Page ────────────────────────────────────────────────────
    public function qrList(Request $request)
    {
        $queryString = $request->server('QUERY_STRING', '');
        parse_str($queryString, $params);

        $group = $params['group'] ?? 'members';

        if (!in_array($group, ['members', 'staff'])) {
            $group = 'members';
        }

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
                ->get(); // FIXED: Added missing -> here
        }

        return view('attendance.qr-list', compact('members', 'staffList', 'group', 'search'));
    }
}
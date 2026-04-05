@extends('layouts.app')

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-bold mb-6">Attendance Log</h1>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-gray-500 uppercase text-xs">
                    <th class="p-4">#</th>
                    <th class="p-4">Member</th>
                    <th class="p-4">Time In</th>
                    <th class="p-4">Time Out</th>
                    <th class="p-4">Duration</th>
                    <th class="p-4">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $i => $log)
                <tr class="border-t hover:bg-gray-50">
                    <td class="p-4">{{ $i + 1 }}</td>
                    <td class="p-4 font-medium">{{ $log->member->full_name }}</td>
                    <td class="p-4">{{ $log->time_in->format('M d, Y H:i') }}</td>
                    <td class="p-4">{{ $log->time_out ? $log->time_out->format('H:i') : '—' }}</td>
                    <td class="p-4 text-gray-500">
                        @if($log->time_out)
                            {{ $log->time_in->diffForHumans($log->time_out, true) }}
                        @else
                            <span class="text-green-600">Still inside</span>
                        @endif
                    </td>
                    <td class="p-4">
                        @if($log->time_out)
                            <span class="bg-gray-200 text-gray-700 text-xs px-2 py-1 rounded-full">Out</span>
                        @else
                            <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full">Inside</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-4">{{ $logs->links() }}</div>
    </div>
</div>
@endsection
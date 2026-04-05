@extends('layouts.app')

@section('content')
<div class="p-6 max-w-lg mx-auto">
    <h1 class="text-2xl font-bold mb-6">QR Scanner</h1>

    <div class="bg-white rounded-xl shadow p-6 text-center">
        <div id="reader" class="mb-4 rounded-lg overflow-hidden"></div>
        <div id="result" class="hidden p-4 rounded-lg mb-4"></div>
        <p class="text-gray-500 text-sm">Point camera at member QR code to record attendance</p>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode"></script>
<script>
const html5QrCode = new Html5Qrcode("reader");
html5QrCode.start(
    { facingMode: "environment" },
    { fps: 10, qrbox: 250 },
    async (decodedText) => {
        html5QrCode.pause();
        const res = await fetch("{{ route('attendance.scan') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ qr_code: decodedText })
        });
        const data = await res.json();
        const el = document.getElementById('result');
        el.className = 'p-4 rounded-lg mb-4 ' +
            (data.success ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800');
        el.textContent = data.message;
        el.classList.remove('hidden');
        setTimeout(() => { el.classList.add('hidden'); html5QrCode.resume(); }, 3000);
    }
);
</script>
@endsection
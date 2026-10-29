@php
    $reverb = config('broadcasting.connections.reverb');
@endphp

<script src="https://cdn.jsdelivr.net/npm/pusher-js@8.6.0/dist/web/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@2.6.3/dist/echo.iife.js"></script>
<script>
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: @json($reverb['key']),
        wsHost: @json($reverb['options']['host']),
        wsPort: @json((int) $reverb['options']['port']),
        wssPort: @json((int) $reverb['options']['port']),
        forceTLS: @json((bool) $reverb['options']['useTLS']),
        enabledTransports: ['ws', 'wss'],
    });
</script>

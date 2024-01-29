<!doctype html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="stylesheet"
          href="{{ url('vendor/oeem-extensions/awf/extension/css/display/shift_management_panel.css') }}">
    <title></title>
</head>
<body>
@if($type == 'start')
    @include('awf-extension::display.shift_management_panel.shift_start')
@endif
@if($type == 'production')
    @include('awf-extension::display.shift_management_panel.production')
@endif
@if($type == 'reason')
    @include('awf-extension::display.shift_management_panel.reason')
@endif

<div class="timer">
    <span>{{ __('display.systemTime') }}:</span>
    <span id="time"></span>
</div>

<script>
    function refreshTime() {
        $('#time').html('')
        const date = new Date()

        var hours = date.getHours()
        var minutes = date.getMinutes()
        var seconds = date.getSeconds()

        hours = ('0' + hours).slice(-2)
        minutes = ('0' + minutes).slice(-2)
        seconds = ('0' + seconds).slice(-2)

        $('#time').append(hours + ':' + minutes + ':' + seconds)
    }

    $(document).ready(function () {
        setInterval(refreshTime, 1000)

        window.Echo.channel('next-product')
            .listen('.next-product-event', (e) => {
                var data = e[0]
            })
    })
</script>
</body>
</html>

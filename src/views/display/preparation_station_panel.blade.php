<!doctype html>
<html lang="{{ explode('_', Session::get('locale'))[0] }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="stylesheet"
          href="{{ url('vendor/oeem-extensions/awf/extension/css/display/preparation_station_panel.css') }}">
    <title></title>
</head>
<body>
<div id="vue-app" style="margin-left: 3vh;">
    <div class="container upper">
        <span class="button center">
            P992
        </span>
    </div>
    <div class="container middle">
        <span class="half" style="padding-left: 7vh;">
            <img class="middle-image" src="{{ url('vendor/oeem-extensions/awf/extension/images/default.png') }}"
                 alt="Product picture">
        </span>
        <span class="half half-right">
            <div class="middle-circle" style="background-color: {{ $nextSequence?->color ?? '#000' }};">
            </div>
        </span>
    </div>
    <div class="container bottom">
        <div class="datas">
            <span class="half half-left piece">
                {{ $nextSequence?->designation ?? 'C-Säule links' }}
            </span>
            <span class="half half-right piece">
                {{ $nextSequence?->materialAndColor ?? 'Dinamica schwarz'}}
            </span>
        </div>
        <div class="timer">
            <span id="time"></span>
        </div>
    </div>
</div>

<div class="hide" style="display: {{ $default === true ? 'block' : 'none' }};">
    <div class="timer">
        <span id="hidden-time"></span>
    </div>
</div>

<script>
    function refreshTime() {
        $('#time').html('')
        $('#hidden-time').html('')
        const date = new Date()

        var hours = date.getHours()
        var minutes = date.getMinutes()
        var seconds = date.getSeconds()

        hours = ('0' + hours).slice(-2)
        minutes = ('0' + minutes).slice(-2)
        seconds = ('0' + seconds).slice(-2)

        $('#time').append(hours + ':' + minutes + ':' + seconds)
        $('#hidden-time').append(hours + ':' + minutes + ':' + seconds)
    }

    $(document).ready(function () {
        setInterval(refreshTime, 1000)

        window.Echo.channel('next-product')
            .listen('.next-product-event', (e) => {
                var data = e[0]
            })
    })
</script>

<script>
    var viewAppletData = {
        nextSequence: JSON.parse('{!! json_encode($nextSequence) !!}'),
    };
</script>
<script src="{!! asset('/dist/vue.js') !!}"></script>
<script src="{!! asset('dist/vue/echo.js') !!}?random_cache_buster={{rand()}}"></script>
<script src="{!! asset('vendor/oeem-extensions/awf/extension/js/PreparationStationPanel.js?cache_buster=').time() !!}"></script>
</body>
</html>

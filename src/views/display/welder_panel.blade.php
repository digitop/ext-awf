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

    @if(env('APP_ENV') === 'production')
        @include('common.inc.production.scripts', ['path'=>asset('dist/production')])
    @else
        @include('common.inc.development.scripts', ['path'=>asset('dist/development')])
    @endif

</head>
<body>
<div id="vue-app">
    <div class="awf-container upper">
        <span class="button center">
            P992
        </span>
    </div>
    <div class="awf-container middle">
        <span class="half" style="padding-left: 7vh;">
            <img class="middle-image" src=""
                 alt="Product picture">
        </span>
        <span class="half half-right">
            <div id="product-color" class="middle-circle" style="background-color: #000;">
            </div>
        </span>
    </div>
    <div class="awf-container bottom">
        <span class="half" style="padding-left: 7vh;">
            <div class="datas">
            <span id="product-designation" class="half half-left piece">
            </span>
            <span id="product-material"  class="half half-right piece">
            </span>
        </div>
        </span>
        <span class="half half-right">
            <div class="datas">
            <span id="product-designation" class="half half-left piece">
            </span>
            <span id="product-material"  class="half half-right piece">
            </span>
        </div>
        </span>
        <div class="timer">
            <span id="time"></span>
        </div>
    </div>
</div>

<div id="hide-app" class="hide">
    <div class="timer-bottom">
        <div class="timer">
            <span id="hidden-time"></span>
        </div>
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
    })
</script>

<script>
    var viewAppletData = {
        welderNextSequence: JSON.parse('{{ json_encode($welderNextSequence) }}'),
    };
</script>
<script src="{!! asset('/dist/vue.js') !!}"></script>
<script src="{!! asset('dist/vue/echo.js') !!}?random_cache_buster={{rand()}}"></script>
<script src="{!! asset('vendor/oeem-extensions/awf/extension/js/WelderPanel.js?cache_buster=').time() !!}"></script>
</body>
</html>

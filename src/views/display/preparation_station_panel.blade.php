<!doctype html>
<html lang="hu">
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
<div id="vue-app">
    <div class="container upper">
        <span class="button left">
            P992
        </span>
        <span class="button right">
            G16
        </span>
    </div>
    <div class="container middle">
        <span class="half">
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
            <div class="half piece">
                {{ $nextSequence?->designation ?? 'C-Säule links' }}
            </div>
            <div class="half half-right piece">
                {{ $nextSequence?->materialAndColor ?? 'Dinamica schwarz'}}
            </div>
        </div>
        <span class="half">
            <table class="half-table">
                <tr>
                    <th class="line-separated">{{ __('display.sequenceIndex') }}:</th>
                    <td></td>
                </tr>
                <tr>
                    <th class="line-separated">{{ __('display.fabricShelf') }}:</th>
                    <td></td>
                </tr>
                <tr>
                    <th class="line-separated">{{ __('display.myInput') }}:</th>
                    <td></td>
                </tr>
                <tr>
                    <th class="line-separated">{{ __('display.myString') }}:</th>
                    <td></td>
                </tr>
                <tr>
                    <th>{{ __('display.isActive') }}:</th>
                    <td></td>
                </tr>
                <tr>
                    <th>{{ __('display.barcode') }}:</th>
                    <td></td>
                </tr>
                <tr>
                    <th>{{ __('display.sqlSyncRun') }}:</th>
                    <td></td>
                </tr>
            </table>
        </span>
        <span class="half half-right">
            <table class="half-table">
                <tr>
                    <th class="line-separated">{{ __('display.sequenceIndex') }}:</th>
                    <td></td>
                </tr>
                <tr>
                    <th class="line-separated">{{ __('display.fabricShelf') }}:</th>
                    <td></td>
                </tr>
            </table>
        </span>
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

<!doctype html>
<html lang="{{ explode('_', Session::get('locale'))[0] }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>

    <link rel="stylesheet"
          href="{{ url('vendor/oeem-extensions/awf/extension/css/display/shift_management_panel.css') }}">

{{--    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">--}}

    @yield('awf-css')
    <title></title>
</head>
<body>
<div class="title">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 4500 300" class="title-svg">
        <title>Porsche</title>
        <path d="M502 221c48.1 0 74-25.9 74-74V74c0-48.1-25.9-74-74-74H0v300h68v-79h434zm6-143v65c0 7.8-4.2 12-12 12H68V66h428c7.8 0 12 4.2 12 12zm228 222c-48.1 0-74-25.9-74-74V74c0-48.1 25.9-74 74-74h417c48.1 0 74 25.9 74 74v152c0 48.1-25.9 74-74 74H736zm411-66c7.8 0 12-4.2 12-12V78c0-7.8-4.2-12-12-12H742c-7.8 0-12 4.2-12 12v144c0 7.8 4.2 12 12 12h405zm675-36c39.844 16.757 67.853 56.1 68 102h-68c0-54-25-79-79-79h-361v79h-68V0h502c48.1 0 74 25.9 74 74v50.14c0 46.06-23.75 71.76-68 73.86zm-12-43c7.8 0 12-4.2 12-12V78c0-7.8-4.2-12-12-12h-428v89h428zm162-81c0-48.1 25.9-74 74-74h492v56h-486c-7.8 0-12 4.2-12 12v42c0 7.8 4.2 12 12 12h422c48.1 0 74 25.9 74 74v30c0 48.1-25.9 74-74 74h-492v-56h486c7.8 0 12-4.2 12-12v-42c0-7.8-4.2-12-12-12h-422c-48.1 0-74-25.9-74-74V74zm661 0c0-48.1 25.9-74 74-74h480v66h-474c-7.8 0-12 4.2-12 12v144c0 7.8 4.2 12 12 12h474v66h-480c-48.1 0-74-25.9-74-74V74zM3817 0v300h-68V183h-407v117h-68V0h68v117h407V0h68zm156 56v66h527v56h-527v66h527v56h-595V0h595v56h-527z">
        </path>
    </svg>

    <img src="{{ url('vendor/oeem-extensions/awf/extension/images/awf-logo.png') }}" alt="AWF logo" class="title-image">
</div>

@yield('awf-shift-content')

<div class="timer">
{{--    <div class="languages menu-icon" onclick="$('.languages-dropdown').slideToggle()">--}}
{{--        <i class="fas fa-globe-americas" style="color: black; font-size: 5vh;"></i>--}}
{{--    </div>--}}

{{--    <div class="languages-dropdown">--}}
{{--        <ul>--}}
{{--            <li><a href="{{ url('lang/hu_HU') }}">Magyar</a></li>--}}
{{--            <li><a href="{{ url('lang/en_US') }}">English</a></li>--}}
{{--            <li><a href="{{ url('lang/de_DE') }}">Deutsch</a></li>--}}
{{--        </ul>--}}
{{--    </div>--}}

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

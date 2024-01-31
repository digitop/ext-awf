@extends('awf-extension::display.shift_management_panel.shift_management_base')

@section('awf-shift-content')

    <div class="content" style="margin-top: 20vh;">
        <div>
            Visszaállítás:
        </div>

        @foreach($dashboards as $dashboard)
            <button id="button{{ $dashboard->DHIDEN }}">{{ $dashboard->DHNAME }}</button>

            <script>
                $('#button{{ $dashboard->DHIDEN }}').bind('click', function () {
                    $.get('http://localhost:8000/api/ext/awf-extension/shift-management/set-default/{{ $dashboard->DHIDEN }}')
                })
            </script>
        @endforeach
    </div>

    <div class="footer">
        <a href="{{ route('awf-shift-management-panel.default') }}" class="back">
            {{ __('display.button.back') }}
        </a>
    </div>
@endsection
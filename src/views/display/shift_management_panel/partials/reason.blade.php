@extends('awf-extension::display.shift_management_panel.shift_management_base')

@section('awf-shift-content')

    <div style="margin-top: 20vh;">
        <div class="reason-title">
            Visszaállítás:
        </div>
        <div class="reason-content">
            @foreach($dashboards as $dashboard)
                <button id="button{{ $dashboard->DHIDEN }}" class="oppanel-button">{{ $dashboard->DHNAME }}</button>

                <script>
                    $('#button{{ $dashboard->DHIDEN }}').bind('click', function () {
                        $.get('{{ env('APP_URL') }} /api/ext/awf-extension/shift-management/set-default/{{ $dashboard->DHIDEN }}')
                    })
                </script>
            @endforeach
        </div>
    </div>

    <div class="footer">
        <a href="{{ route('awf-shift-management-panel.default') }}" class="back">
            {{ __('display.button.back') }}
        </a>
    </div>
@endsection

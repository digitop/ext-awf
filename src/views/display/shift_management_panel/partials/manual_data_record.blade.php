@extends('awf-extension::display.shift_management_panel.shift_management_base')

@section('awf-css')

@endsection

@section('awf-shift-content')
    <div style="margin-top: 20vh;">
    <div class="reason-content">
        @foreach($workCenters as $workCenter)
            <a
                    href="{{ route('awf-shift-management-panel.manual-data-record.show', ['WCSHNA' => $workCenter->WCSHNA]) }}"
                    class="oppanel-button"
                    style="display: inline-block;"
            >
                {{ $workCenter->WCNAME }}
            </a>
        @endforeach
    </div>
    </div>

    <div class="footer">
        <a href="{{ route('awf-shift-management-panel.default') }}" class="back">
            {{ __('display.button.back') }}
        </a>
    </div>
@endsection

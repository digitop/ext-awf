@extends('awf-extension::display.shift_management_panel.shift_management_base')

@section('awf-shift-content')
    reaseon
    <div class="footer">
        <a href="{{ route('awf-shift-management-panel.default') }}" class="back">
            {{ __('display.button.back') }}
        </a>
    </div>
@endsection
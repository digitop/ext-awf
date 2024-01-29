@extends('awf-extension::display.shift_management_panel.shift_management_base')

@section('awf-shift-content')
    production
    <div class="footer">
        <a href="{{ url()->previous() }}" class="back">
            {{ __('display.button.back') }}
        </a>
    </div>
@endsection
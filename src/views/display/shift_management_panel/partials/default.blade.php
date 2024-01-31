@extends('awf-extension::display.shift_management_panel.shift_management_base')

@section('awf-shift-content')
    <table class="shift-management-table">
        <tr>
            <td style="width: 25%;">
                <a class="button button-blue" href="{{ route('awf-shift-management-panel.shift-start') }}">
                    {{ __('display.button.shiftStart') }}
                </a>
            </td>
            <td style="width: 35%;">
                <a class="button button-green" href="{{ route('awf-shift-management-panel.production') }}">
                    {{ __('display.button.production') }}
                </a>
            </td>
            <td style="width: 40%;">
                <a class="button button-red" href="{{ route('awf-shift-management-panel.reason') }}">
                    {{ __('display.button.reason') }}
                </a>
            </td>
        </tr>
    </table>
@endsection
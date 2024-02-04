@extends('awf-extension::display.shift_management_panel.shift_management_base')

@section('awf-shift-content')
    <table class="shift-management-table default">
        <tr>
            <td style="width: 30%;">
                <a class="button button-blue" href="{{ route('awf-shift-management-panel.shift-start') }}">
                    {{ __('display.button.shiftStart') }}
                </a>
            </td>
            <td style="width: 30%;">
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
        <tr>
            <td colspan="2" style="width: 60%; padding-top: 5vh;">
                <a class="button button-red" href="{{ route('awf-shift-management-panel.reason') }}">
                    {{ __('display.button.productLifecycle') }}
                </a>
            </td>
            <td style="width: 40%; padding-top: 5vh;">
                <a class="button button-green" href="{{ route('awf-shift-management-panel.production') }}">
                    {{ __('display.button.manualProductSave') }}
                </a>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="width: 60%; padding-top: 5vh;">
                <a class="button button-light-green" href="{{ route('awf-shift-management-panel.production') }}">
                    {{ __('display.button.sequenceUpdate') }}
                </a>
            </td>
            <td style="width: 40%; padding-top: 5vh;">
            </td>
        </tr>
    </table>
@endsection
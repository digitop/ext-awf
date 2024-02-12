@extends('awf-extension::display.shift_management_panel.shift_management_base')

@section('awf-shift-content')
    <div class="page-title" style="margin-top: 12%;">
        {{ __('display.button.shiftStart') }}
    </div>
    <table class="shift-management-table start-shift">
        <thead>
        <tr>
            <th></th>
            <th>{{ __('awf-extension::display.data.shift-sequence.porscheOrderNumber') }}</th>
            <th>{{ __('awf-extension::display.data.shift-sequence.porscheSequenceNumber') }}</th>
            <th>{{ __('awf-extension::display.data.shift-sequence.articleNumber') }}</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th style="width: 10%;">A-{{ __('awf-extension::display.pillar') }}</th>
            @if(!empty($aPillar))
                <td>{{ $aPillar->SEPONR }}</td>
                <td>{{ $aPillar->SEPSEQ }}</td>
                <td>{{ $aPillar->SEARNU }}</td>
                <td>
                    <a class="button-small button-blue" href="{{ route('awf-shift-management-panel.shift-start.index', ['pillar' => 'A']) }}">
                        {{ __('button.operations') }}
                    </a>
                </td>
            @else
                <td colspan="4">{{ __('display.noData') }}</td>
            @endif
        </tr>
        <tr>
            <th style="width: 10%;">B-{{ __('awf-extension::display.pillar') }}</th>
            @if(!empty($bPillar))
                <td>{{ $bPillar->SEPONR }}</td>
                <td>{{ $bPillar->SEPSEQ }}</td>
                <td>{{ $bPillar->SEARNU }}</td>
                <td>
                    <a class="button-small button-blue" href="{{ route('awf-shift-management-panel.shift-start.index', ['pillar' => 'B']) }}">
                        {{ __('button.operations') }}
                    </a>
                </td>
            @else
                <td colspan="4">{{ __('display.noData') }}</td>
            @endif
        </tr>
        <tr>
            <th style="width: 10%;">C-{{ __('awf-extension::display.pillar') }}</th>
            @if(!empty($cPillar))
                <td>{{ $cPillar->SEPONR }}</td>
                <td>{{ $cPillar->SEPSEQ }}</td>
                <td>{{ $cPillar->SEARNU }}</td>
                <td>
                    <a class="button-small button-blue" href="{{ route('awf-shift-management-panel.shift-start.index', ['pillar' => 'C']) }}">
                        {{ __('button.operations') }}
                    </a>
                </td>
            @else
                <td colspan="4">{{ __('display.noData') }}</td>
            @endif
        </tr>
        </tbody>
    </table>

    <div style="margin-top: 20%; margin-left: 12%;">
{{--        <button--}}
{{--            id="reset-default"--}}
{{--            class="button button-green"--}}
{{--            style="width: 80%;"--}}
{{--        >--}}
{{--            {{ __('display.button.startOfShift') }}--}}
{{--        </button>--}}

        <script>
            $('#reset-default').bind('click', function () {
                $.get('{{ route('awf-shift-management-panel.shift-start.default') }}')
            })
        </script>
    </div>

    <div class="footer">
        <a href="{{ route('awf-shift-management-panel.default') }}" class="back">
            {{ __('display.button.back') }}
        </a>
    </div>
@endsection
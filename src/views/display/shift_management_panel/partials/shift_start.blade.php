@extends('awf-extension::display.shift_management_panel.shift_management_base')

@section('awf-shift-content')
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
            <td>{{ $aPillar->SEPONR }}</td>
            <td>{{ $aPillar->SEPSEQ }}</td>
            <td>{{ $aPillar->SEARNU }}</td>
            <td>
                <a class="button-small button-blue" href="{{ route('awf-shift-management-panel.shift-start.index', ['pillar' => 'A']) }}">
                    {{ __('button.operations') }}
                </a>
            </td>
        </tr>
        <tr>
            <th style="width: 10%;">B-{{ __('awf-extension::display.pillar') }}</th>
            <td>{{ $bPillar->SEPONR }}</td>
            <td>{{ $bPillar->SEPSEQ }}</td>
            <td>{{ $bPillar->SEARNU }}</td>
            <td>
                <a class="button-small button-blue" href="{{ route('awf-shift-management-panel.shift-start.index', ['pillar' => 'B']) }}">
                    {{ __('button.operations') }}
                </a>
            </td>
        </tr>
        <tr>
            <th style="width: 10%;">C-{{ __('awf-extension::display.pillar') }}</th>
            <td>{{ $cPillar->SEPONR }}</td>
            <td>{{ $cPillar->SEPSEQ }}</td>
            <td>{{ $cPillar->SEARNU }}</td>
            <td>
                <a class="button-small button-blue" href="{{ route('awf-shift-management-panel.shift-start.index', ['pillar' => 'C']) }}">
                    {{ __('button.operations') }}
                </a>
            </td>
        </tr>
        </tbody>
    </table>

    <div class="footer">
        <a href="{{ route('awf-shift-management-panel.default') }}" class="back">
            {{ __('display.button.back') }}
        </a>
    </div>
@endsection
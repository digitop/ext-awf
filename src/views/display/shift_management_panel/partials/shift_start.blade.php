@extends('awf-extension::display.shift_management_panel.shift_management_base')

@section('awf-css')
    <link rel="stylesheet"
          href="{{ url('vendor/oeem-extensions/awf/extension/css/display/loading.css') }}">
    <link rel="stylesheet"
          href="{{ url('vendor/oeem-extensions/awf/extension/css/display/modal.css') }}">
@endsection

@section('awf-shift-content')
    <div id="vue-app" v-on:click="closeModal">
        <section id="loading">
            <div id="loading-content"></div>
        </section>

        <div id="successModal" class="modal">
            <div class="modal-content modal-success">
                <p style="text-align: center; margin-top: 5%">
                    @include('awf-extension::success', ['class' => 'success-sign'])
                </p>
            </div>
        </div>

        <div id="warningModal" class="modal">
            <div class="modal-content modal-warning">
                <p style="text-align: center; margin-top: 5%; font-size: xxx-large;">
                    {{ __('display.waring-message', ['action' => __('display.button.shiftStart')]) }}?
                </p>
                <button class="button button-red button-modal"
                        id="success-shift-start-button"
                        v-on:click="successShiftButtonClick"
                >
                    {{ __('display.button.confirm') }}
                </button>
            </div>
        </div>

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
                        <a class="button-small button-blue"
                           href="{{ route('awf-shift-management-panel.shift-start.index', ['pillar' => 'A']) }}">
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
                        <a class="button-small button-blue"
                           href="{{ route('awf-shift-management-panel.shift-start.index', ['pillar' => 'B']) }}">
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
                        <a class="button-small button-blue"
                           href="{{ route('awf-shift-management-panel.shift-start.index', ['pillar' => 'C']) }}">
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
            <button
                    v-on:click="startOfShiftButtonClick"
                    id="reset-default"
                    class="button button-green"
                    style="width: 80%;"
            >
                {{ __('display.button.startOfShift') }}
            </button>
        </div>

        <div class="footer">
            <a href="{{ route('awf-shift-management-panel.default') }}" class="back">
                {{ __('display.button.back') }}
            </a>
        </div>
    </div>

    <script>
        var viewAppletData = {
            buttonUrl: '{{ route('awf-shift-management-panel.shift-start.default') }}',
        };
    </script>
    <script src="{!! asset('/dist/vue.js') !!}"></script>
    <script src="{!! asset('dist/vue/echo.js') !!}?random_cache_buster={{rand()}}"></script>
    <script src="{!! asset('vendor/oeem-extensions/awf/extension/js/ShiftStart.js?cache_buster=').time() !!}"></script>
@endsection
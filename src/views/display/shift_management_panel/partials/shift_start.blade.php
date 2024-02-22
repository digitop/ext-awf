@extends('awf-extension::display.shift_management_panel.shift_management_base')

@section('awf-css')
    <link rel="stylesheet"
          href="{{ url('vendor/oeem-extensions/awf/extension/css/display/loading.css') }}">
    <link rel="stylesheet"
          href="{{ url('vendor/oeem-extensions/awf/extension/css/display/modal.css') }}">
@endsection

@section('awf-shift-content')
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
            <button class="button button-red button-modal" id="success-shift-start-button">{{ __('display.button.confirm') }}</button>
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
                id="reset-default"
                class="button button-green"
                style="width: 80%;"
        >
            {{ __('display.button.startOfShift') }}
        </button>

        <script>
            var successModal = $('#successModal')
            var warningModal = $('#warningModal')

            function showWarning() {
                warningModal.css('display', 'block')
            }

            $(document).click(function () {
                if (successModal.css('display') == 'block') {
                    successModal.css('display', 'none')
                }
                if (warningModal.css('display') == 'block') {
                    warningModal.css('display', 'none')
                }
            })

            $('#reset-default').bind('click', function () {
                setTimeout(showWarning, 100)
            })

            $('#success-shift-start-button').bind('click', function () {
                warningModal.css('display', 'none')
                showLoading()

                $.get('{{ route('awf-shift-management-panel.shift-start.default') }}', function (response) {
                    console.log('response: ', response)
                    if (response.success === true) {
                        successModal.css('display', 'block')
                    }
                })
                    .always(function () {
                        hideLoading()
                    })
            })

            function showLoading() {
                document.querySelector('#loading').classList.add('loading')
                document.querySelector('#loading-content').classList.add('loading-content')
            }

            function hideLoading() {
                document.querySelector('#loading').classList.remove('loading')
                document.querySelector('#loading-content').classList.remove('loading-content')
            }
        </script>
    </div>

    <div class="footer">
        <a href="{{ route('awf-shift-management-panel.default') }}" class="back">
            {{ __('display.button.back') }}
        </a>
    </div>
@endsection
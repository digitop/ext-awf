@extends('awf-extension::display.shift_management_panel.shift_management_base')

@section('awf-css')
    <link rel="stylesheet"
          href="{{ url('vendor/oeem-extensions/awf/extension/css/display/manual_data_record_workcenter.css') }}">
    <link rel="stylesheet"
          href="{{ url('vendor/oeem-extensions/awf/extension/css/display/production_workcenter.css') }}">
    <link rel="stylesheet"
          href="{{ url('vendor/oeem-extensions/awf/extension/css/display/loading.css') }}">
    <link rel="stylesheet"
          href="{{ url('vendor/oeem-extensions/awf/extension/css/display/modal.css') }}">
@endsection

@section('awf-shift-content')
    <div class="page-title" style="margin-top: 10%;">
        {{ $workCenter->WCNAME }}
    </div>

    <div id="successModal" class="modal">
        <div class="modal-content modal-success">
            <p style="text-align: center; margin-top: 5%">
                @include('awf-extension::success', ['class' => 'success-sign'])
            </p>
        </div>
    </div>

    <section id="loading">
        <div id="loading-content"></div>
    </section>
    <div style="margin-top: 5%;">
        <div class="content">
            @if(!$data['data']->isEmpty())
                <select class="cdpvars" name="cdpvars" id="cdpvars">
                    @foreach($data['data'] as $item)
                        <option value="{{ $item->CVNAME }}">{{ $item->CVDESC }}</option>
                    @endforeach
                </select>

                <button id="reset-default"
                        class="button button-green"
                        style="margin-left: 5%; padding: 0.5% 2%; font-size: xx-large;"
                >
                    {{ __('display.button.confirm') }}
                </button>
            @else
                <label>
                    <input class="alertCheckbox" autocomplete="off"/>
                    <div class="alert warning" style="cursor: default !important; margin-top: 10%;">
                    <span class="alertText">
                        {{ __('display.noData') }}
                        <br class="clear"/>
                    </span>
                    </div>
                </label>
            @endif
        </div>
    </div>

    <div class="footer">
        <a href="{{ route('awf-shift-management-panel.plc-reset') }}" class="back">
            {{ __('display.button.back') }}
        </a>
    </div>

    <script>
        var successModal = $('#successModal')

        $(document).click(function () {
            if (successModal.css('display') == 'block') {
                successModal.css('display', 'none')
            }
        })

        $('#reset-default').bind('click', function () {
            showLoading()

            var color = $('#cdpvars option:selected').val()

            $.post('{{ route('awf-shift-management-panel.plc-reset.reset', ['WCSHNA' => $data['WCSHNA']]) }}',
                {
                    cdpvars: color
                },
                function (response) {
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
            document.querySelector('#loading').classList.add('loading');
            document.querySelector('#loading-content').classList.add('loading-content');
        }

        function hideLoading() {
            document.querySelector('#loading').classList.remove('loading');
            document.querySelector('#loading-content').classList.remove('loading-content');
        }
    </script>
@endsection

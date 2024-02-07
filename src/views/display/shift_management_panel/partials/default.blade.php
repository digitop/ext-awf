@extends('awf-extension::display.shift_management_panel.shift_management_base')

@section('awf-css')
    <link rel="stylesheet"
          href="{{ url('vendor/oeem-extensions/awf/extension/css/display/loading.css') }}">
    <link rel="stylesheet"
          href="{{ url('vendor/oeem-extensions/awf/extension/css/display/modal.css') }}">
    <link rel="stylesheet"
          href="{{ url('vendor/oeem-extensions/awf/extension/css/display/svg.css') }}">
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

    <div id="errorModal" class="modal">
        <div class="modal-content modal-error">
            <p style="text-align: center; margin-top: 5%">
                @include('awf-extension::error', ['class' => 'success-sign'])
            </p>
        </div>
    </div>

    <table class="shift-management-table default">
        <tr>
            <td style="width: 25%;">
                <a class="button button-blue" href="{{ route('awf-shift-management-panel.shift-start') }}">
                    {{ __('display.button.shiftStart') }}
                </a>
            </td>
            <td style="width: 30%;">
                <a class="button button-green" href="{{ route('awf-shift-management-panel.production') }}">
                    {{ __('display.button.production') }}
                </a>
            </td>
            <td style="width: 45%;">
                <a class="button button-red" href="{{ route('awf-shift-management-panel.reason') }}">
                    {{ __('display.button.reason') }}
                </a>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="width: 55%; padding-top: 5vh;">
                <button class="button button-light-green" id="sequenceUpdate">
                    {{ __('display.button.sequenceUpdate') }}
                </button>
            </td>
            <td style="width: 45%; padding-top: 5vh;">
                <a class="button button-green" href="{{ route('awf-shift-management-panel.manual-data-record') }}">
                    {{ __('display.button.manualProductSave') }}
                </a>
            </td>
        </tr>
    </table>

    <script>
        var successModal = $('#successModal')
        var errorModal = $('#errorModal')

        $(document).click(function () {
            if (successModal.css('display') == 'block') {
                successModal.css('display', 'none')
            }
            if (errorModal.css('display') == 'block') {
                errorModal.css('display', 'none')
            }
        })

        $('#sequenceUpdate').bind('click', function () {
            showLoading()

            $.get('{{ route('awf-generate-data.create') }}', function (createResponse) {
                if (createResponse.success === true) {
                    $.get('{{ route('awf-generate-data.create') }}', function (orderResponse) {
                        if (orderResponse.success === true) {
                            successModal.css('display', 'block')
                        }
                        else if (orderResponse.success === false) {
                            errorModal.css('display', 'block')
                        }
                    })
                }
                else if (createResponse.success === false) {
                    errorModal.css('display', 'block')
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
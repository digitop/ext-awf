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

    <div style="margin-top: 20vh;">
        <div class="page-title">
            {{ __('display.button.reason') }}
        </div>
        <div class="reason-content">
            @foreach($dashboards as $dashboard)
                <div id="successModal{{ $dashboard->DHIDEN }}" class="modal">
                    <div class="modal-content modal-success">
                        <p style="text-align: center; margin-top: 5%">
                            @include('awf-extension::success', ['class' => 'success-sign'])
                        </p>
                    </div>
                </div>

                <button id="button{{ $dashboard->DHIDEN }}" class="oppanel-button">{{ $dashboard->DHNAME }}</button>

                <script>
                    var successModal = $('#successModal{{ $dashboard->DHIDEN }}')

                    $(document).click(function () {
                        if (successModal.css('display') == 'block') {
                            successModal.css('display', 'none')
                        }
                    })

                    $('#button{{ $dashboard->DHIDEN }}').bind('click', function () {
                        showLoading()

                        $.get('{{ env('APP_URL') }}/api/ext/awf-extension/shift-management/set-default/{{ $dashboard->DHIDEN }}', function (response) {
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
            @endforeach
        </div>
    </div>

    <div class="footer">
        <a href="{{ route('awf-shift-management-panel.default') }}" class="back">
            {{ __('display.button.back') }}
        </a>
    </div>
@endsection

@extends('awf-extension::display.shift_management_panel.shift_management_base')

@section('awf-css')
    <link rel="stylesheet"
          href="{{ url('vendor/oeem-extensions/awf/extension/css/display/production_workcenter.css') }}">
    <link rel="stylesheet"
          href="{{ url('vendor/oeem-extensions/awf/extension/css/display/loading.css') }}">
@endsection

@section('awf-shift-content')
    <section id="loading">
        <div id="loading-content"></div>
    </section>
    <div style="margin-top: 16%;">
        @if(empty($data))
            <label>
                <input class="alertCheckbox" autocomplete="off" />
                <div class="alert warning" style="cursor: default !important; margin-top: 10%;">
                    <span class="alertText">
                        {{ __('display.noSequence') }}
                        <br class="clear"/>
                    </span>
                </div>
            </label>
        @else
            <div  class="production-content">
                <table id="production-table" class="shift-management-table production-table">
                    <thead>
                    <tr>
                        <th>{{ __('display.data.shift-sequence.sequenceId') }}</th>
                        <th>{{ __('display.data.shift-sequence.porscheOrderNumber') }}</th>
                        <th>{{ __('display.data.shift-sequence.porscheSequenceNumber') }}</th>
                        <th>{{ __('display.data.shift-sequence.articleNumber') }}</th>
                        <th>{{ __('display.orderCode') }}</th>
                    </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="footer">
        <a href="{{ route('awf-shift-management-panel.production') }}" class="back">
            {{ __('display.button.back') }}
        </a>
    </div>

    <script>
        function refreshData() {
            const timeout = 15000
            let html = ''

            $.ajax({
                url: '{{ route('awf-shift-management-panel.production.data', ['WCSHNA' => $data['WCSHNA']]) }}',
                dataType: 'json',
                method: 'get',
                timeout: timeout,
                beforeSend: function () {
                    showLoading()
                },
                success: function (response) {
                    let hasGotOver = false
                    let hasInPlace = false
                    let hasWaiting = false

                    hideLoading()

                    $.each(response.data, function (key, data) {
                        if (typeof response.data?.gotOver == 'undefined' && !hasGotOver) {
                            html += '<tr class="awf-sequence-got-over"><td colspan="5">{{ __('display.noData') }}</td></tr>'
                            hasGotOver = true
                        }
                        else if (typeof response.data?.gotOver != 'undefined' && !hasGotOver) {
                            if (key == 'gotOver') {
                                html += '<tr class="awf-sequence-got-over"><td>' + data.SEQUID + '</td><td>' + data.SEPONR + '</td><td>' + data.SEPSEQ + '</td><td>' + data.PRCODE + '</td><td>' + data.ORCODE + '</td></tr>'
                                hasGotOver = true
                            }
                        }

                        if (typeof response.data?.inPlace == 'undefined' && !hasInPlace) {
                            html += '<tr class="awf-sequence-in-place"><td colspan="5">{{ __('display.noData') }}</td></tr>'
                            hasInPlace = true
                        }
                        else if (typeof response.data?.inPlace != 'undefined' && !hasInPlace) {
                            if (key == 'inPlace') {
                                html += '<tr class="awf-sequence-in-place"><td>' + data.SEQUID + '</td><td>' + data.SEPONR + '</td><td>' + data.SEPSEQ + '</td><td>' + data.PRCODE + '</td><td>' + data.ORCODE + '</td></tr>'
                                hasInPlace = true
                            }
                        }

                        if (typeof response.data?.waitings == 'undefined' && !hasWaiting) {
                            html += '<tr class="awf-sequence-waitings"><td colspan="5">{{ __('display.noData') }}</td></tr>'
                            hasWaiting = true
                        }
                        else if (typeof response.data?.waitings != 'undefined' && !hasWaiting) {
                            if (key == 'waitings') {
                                $.each(data, function (key, waiting) {
                                    html += '<tr class="awf-sequence-waiting"><td>' + waiting.SEQUID + '</td><td>' + waiting.SEPONR + '</td><td>' + waiting.SEPSEQ + '</td><td>' + waiting.PRCODE + '</td><td>' + waiting.ORCODE + '</td></tr>'
                                })
                                hasWaiting = true
                            }
                        }
                    })

                    $('#production-table tbody').html(html)

                    setTimeout(function () {
                        refreshData()
                    }, response.timeout)
                }
            })
        }

        function showLoading() {
            document.querySelector('#loading').classList.add('loading');
            document.querySelector('#loading-content').classList.add('loading-content');
        }

        function hideLoading() {
            document.querySelector('#loading').classList.remove('loading');
            document.querySelector('#loading-content').classList.remove('loading-content');
        }

        refreshData()
    </script>
@endsection

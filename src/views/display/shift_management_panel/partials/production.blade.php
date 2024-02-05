@extends('awf-extension::display.shift_management_panel.shift_management_base')

@section('awf-css')
    <link rel="stylesheet"
          href="{{ url('vendor/oeem-extensions/awf/extension/css/display/loading.css') }}">
@endsection

@section('awf-shift-content')
    <section id="loading">
        <div id="loading-content"></div>
    </section>

    <style>
        .even {
            background-color: #fff;
        }

        .odd {
            background-color: #bbbdc2;
        }
    </style>

    <table id="production-table" style="margin-top: 15vh; margin-left: 20vh; width: 80%; text-align: center; font-size: 1.8vh; background-color: white;">
        <thead>
        <tr>
            <th style="border-bottom: 1px solid black;"></th>
            <th style="border-bottom: 1px solid black;">Porsche termék kód</th>
            <th style="border-bottom: 1px solid black;">Porsche szekvencia szám</th>
            <th style="border-bottom: 1px solid black;">Oszlop</th>
            <th style="border-bottom: 1px solid black;">Oldal</th>
            <th style="border-bottom: 1px solid black;">Termék</th>
            <th style="border-bottom: 1px solid black;">Szín</th>
            <th style="border-bottom: 1px solid black;">Anyag</th>
            <th style="border-bottom: 1px solid black; width: 12%;">Szekvencia mennyiség (30 nap)</th>
            <th style="border-bottom: 1px solid black;">Ebből gyártott</th>
        </tr>
        </thead>
        <tbody>
        @php($i = 1)
        @foreach($data as $workCenter => $values)
            <tr class="{{ $i % 2 == 0 ? 'odd': 'even' }}">
                <th>{{ $workCenter }}</th>
                @if(empty($values) || !array_key_exists('porscheProduct', $values))
                    <td colspan="7"></td>
                    <td>{{ $values['monthly'] }}</td>
                    <td>{{ $values['monthlyInProd'] }}</td>
                @else
                    <td>{{ $values['porscheProduct'] }}</td>
                    <td>{{ $values['porscheSequence'] }}</td>
                    <td>{{ $values['pillar'] }}</td>
                    <td>{{ $values['side'] }}</td>
                    <td>{{ $values['product'] }}</td>
                    <td><div style="width: 2.5vh; height: 2.5vh; border-radius: 50%; background-color: {{ '#' . $values['productColor'] }}; margin-left: 30%;"></div></td>
                    <td>{{ $values['productMaterial'] }}</td>
                    <td>{{ $values['monthly'] }}</td>
                    <td>{{ $values['monthlyInProd'] }}</td>
                @endif
            </tr>
            @php($i++)
        @endforeach
        </tbody>
    </table>

    <div class="footer">
        <a href="{{ route('awf-shift-management-panel.default') }}" class="back">
            {{ __('display.button.back') }}
        </a>
    </div>

    <script>
        var timeout = 10000

        function refreshData() {
            $.ajax({
                url: '{{ route('awf-shift-management-panel.production.get') }}',
                dataType: 'json',
                method: 'get',
                timeout: timeout,
                beforeSend: function () {
                    showLoading()
                },
                success: function (response) {
                    console.log(response.data)

                    let html = ''
                    let i = 0

                    $.each(response.data, function (key, value) {
                        html += '<tr class="' + (i % 2 == 0 ? 'odd' : 'even') + '">'
                        html += '<th>' + key + '</th>'

                        if (typeof value?.porscheProduct != 'undefined') {
                            html += '<td>' + value.porscheProduct + '</td>'
                            html += '<td>' + value.porscheSequence + '</td>'
                            html += '<td>' + value.pillar + '</td>'
                            html += '<td>' + value.side + '</td>'
                            html += '<td>' + value.product + '</td>'
                            html += '<td><div style="width: 2.5vh; height: 2.5vh; border-radius: 50%; background-color: #' + value.productColor + '; margin-left: 30%;"></div></td>'
                            html += '<td>' + value.productMaterial + '</td>'
                            html += '<td>' + value.monthly + '</td>'
                            html += '<td>' + value.monthlyInProd + '</td>'
                        }
                        else {
                            html += '<td colspan="7"></td>'
                            html += '<td>' + value.monthly + '</td>'
                            html += '<td>' + value.monthlyInProd + '</td>'
                        }

                        html += '</tr>'

                        $('#production-table tbody').html(html)

                        i++
                    })

                    hideLoading()

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

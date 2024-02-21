@extends('awf-extension::display.shift_management_panel.shift_management_base')

@section('awf-css')
    <link rel="stylesheet"
          href="{{ url('vendor/oeem-extensions/awf/extension/css/display/manual_data_record_workcenter.css') }}">
    <link rel="stylesheet"
          href="{{ url('vendor/oeem-extensions/awf/extension/css/display/loading.css') }}">
    <link rel="stylesheet"
          href="{{ url('vendor/oeem-extensions/awf/extension/css/display/modal.css') }}">
@endsection

@section('awf-shift-content')
    @if (session('error'))
        @php($error = session('error'))
        <label>
            <input type="checkbox" class="alertCheckbox" autocomplete="off"/>
            <div class="alert warning">
                <span class="alertClose">X</span>
                <span class="alertText">
                    @if(is_array($error))
                        @foreach($error as $message)
                            {{ $message . '<br/>' }}
                        @endforeach
                    @else
                        {{ $error }}
                    @endif
                    <br class="clear"/>
                </span>
            </div>
        </label>
    @elseif (session('error') !== null && empty(session('error')))
        <label>
            <input type="checkbox" class="alertCheckbox" autocomplete="off"/>
            <div class="alert success">
                <span class="alertClose">X</span>
                <span class="alertText">
                    Sikeres adatrögzítés!
                    <br class="clear"/>
                </span>
            </div>
        </label>
    @endif

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

    <div style="margin-top: 20vh;">
        @if(empty($sequence))
            <label>
                <input class="alertCheckbox" autocomplete="off"/>
                <div class="alert warning" style="cursor: default !important; margin-top: 10%;">
                    <span class="alertText">
                        {{ __('display.noSequence') }}
                        <br class="clear"/>
                    </span>
                </div>
            </label>
        @else
            <form method="post"
                  action="{{ route('awf-shift-management-panel.manual-data-record.update', ['WCSHNA' => $sequence->WCSHNA]) }}">
                @csrf

                <table>
                    <tr>
                        <th>{{ __('display.workCenter') }}</th>
                        <td>{{ $sequence->WCSHNA }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('display.orderCode') }}</th>
                        <td>{{ $sequence->ORCODE }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('display.side') }}</th>
                        <td>
                            {{ __('display.sideCode.' . $sequence->SESIDE) }}
                        </td>
                    </tr>
                    <tr>
                        <th>{{ __('display.pillar') }}</th>
                        <td>{{ __('display.pillarCode.' . $sequence->SEPILL) }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('display.data.shift-sequence.porscheOrderNumber') }}</th>
                        <td>{{ $sequence->SEPONR }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('display.data.shift-sequence.porscheSequenceNumber') }}</th>
                        <td>{{ $sequence->SEPSEQ }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('display.data.shift-sequence.articleNumber') }}</th>
                        <td>{{ $sequence->PRCODE }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('display.serialNumber') }}</th>
                        <td>{{ $sequence->SNSERN }}</td>
                    </tr>
                </table>

                <div style="margin-top: 5%;">
                    <div>
                        <input name="serialNumber"
                               type="text"
                               title="{{ !empty($sequence->SNSERN) ? __('display.alreadyHasSerial') : '' }}"
                               class="awf-work-center"
                                {{ !empty($sequence->SNSERN) ? 'disabled' : '' }}
                        />
                    </div>
                    <div style="margin-top: 2%;">
                        <button class="awf-work-center-button"
                                type="submit"
                                title="{{ !empty($sequence->SNSERN) ? __('display.alreadyHasSerial') : '' }}"
                                {{ !empty($sequence->SNSERN) ? 'disabled' : '' }}
                        >
                            {{ __('display.button.submit') }}
                        </button>
                    </div>
                </div>
            </form>

            @if(!empty($sequence->SNSERN))
                <button id="awf-move-sequence-button" class="button button-blue awf-move-sequence-button"
                        type="submit"
                >
                    {{ __('display.button.next-work-center') }}
                </button>

                <script>
                    var successModal = $('#successModal')

                    $(document).click(function () {
                        if (successModal.css('display') == 'block') {
                            successModal.css('display', 'none')
                        }
                    })

                    $('#awf-move-sequence-button').bind('click', function () {
                        showLoading()

                        $.post(
                            '{{ route('awf-sequence.store') }}',
                            {
                                WCSHNA:'{{ $sequence->WCSHNA }}',
                                SEQUID:'{{ $sequence->SEQUID }}'
                            },
                            function (data) {
                                if (data?.success == true) {
                                    successModal.css('display', 'block')
                                    location.reload()
                                }
                            }
                        )
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
            @endif
        @endif
    </div>

    <div class="footer">
        <a href="{{ route('awf-shift-management-panel.manual-data-record') }}" class="back">
            {{ __('display.button.back') }}
        </a>
    </div>
@endsection

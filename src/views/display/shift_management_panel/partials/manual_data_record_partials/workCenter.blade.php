@extends('awf-extension::display.shift_management_panel.shift_management_base')

@section('awf-css')
    <link rel="stylesheet"
          href="{{ url('vendor/oeem-extensions/awf/extension/css/display/manual_data_record_workcenter.css') }}">
@endsection

@section('awf-shift-content')
    @if (!empty($error))
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
                <br class="clear"/></span>
            </div>
        </label>
    @endif

    <div style="margin-top: 20vh;">
        @if(empty($sequence))
            <div>{{ __('display.noSequence') }}</div>
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
                </table>

                <div style="margin-top: 5%;">
                    <input name="serialnumber" type="text" class="awf-work-center"/>
                    <button class="awf-work-center-button" type="submit">{{ __('display.button.submit') }}</button>
                </div>
            </form>
        @endif
    </div>

    <div class="footer">
        <a href="{{ url()->previous() }}" class="back">
            {{ __('display.button.back') }}
        </a>
    </div>
@endsection

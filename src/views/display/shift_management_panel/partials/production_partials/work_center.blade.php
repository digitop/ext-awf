@extends('awf-extension::display.shift_management_panel.shift_management_base')

@section('awf-css')
    <link rel="stylesheet"
          href="{{ url('vendor/oeem-extensions/awf/extension/css/display/production_workcenter.css') }}">
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
                    <br class="clear"/>
                </span>
            </div>
        </label>
    @elseif (isset($error) && empty($error))
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
                <table class="shift-management-table production-table">
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
                    @if(array_key_exists('gotOver', $data))
                        <tr class="awf-sequence-got-over">
                            <td>{{ $data['gotOver']->SEQUID }}</td>
                            <td>{{ $data['gotOver']->SEPONR }}</td>
                            <td>{{ $data['gotOver']->SEPSEQ }}</td>
                            <td>{{ $data['gotOver']->PRCODE }}</td>
                            <td>{{ $data['gotOver']->ORCODE }}</td>
                        </tr>
                    @else
                        <tr class="awf-sequence-got-over">
                            <td colspan="5">{{ __('display.noData') }}</td>
                        </tr>
                    @endif
                    @if(array_key_exists('inPlace', $data))
                        <tr class="awf-sequence-in-place">
                            <td>{{ $data['inPlace']->SEQUID }}</td>
                            <td>{{ $data['inPlace']->SEPONR }}</td>
                            <td>{{ $data['inPlace']->SEPSEQ }}</td>
                            <td>{{ $data['inPlace']->PRCODE }}</td>
                            <td>{{ $data['inPlace']->ORCODE }}</td>
                        </tr>
                    @else
                        <tr class="awf-sequence-in-place">
                            <td colspan="5">{{ __('display.noData') }}</td>
                        </tr>
                    @endif
                    @if(array_key_exists('waitings', $data))
                        @foreach($data['waitings'] as $waiting)
                            <tr class="awf-sequence-waiting">
                                <td>{{ $waiting->SEQUID }}</td>
                                <td>{{ $waiting->SEPONR }}</td>
                                <td>{{ $waiting->SEPSEQ }}</td>
                                <td>{{ $waiting->PRCODE }}</td>
                                <td>{{ $waiting->ORCODE }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr class="awf-sequence-waiting">
                            <td colspan="5">{{ __('display.noData') }}</td>
                        </tr>
                    @endif
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="footer">
        <a href="{{ route('awf-shift-management-panel.manual-data-record') }}" class="back">
            {{ __('display.button.back') }}
        </a>
    </div>
@endsection

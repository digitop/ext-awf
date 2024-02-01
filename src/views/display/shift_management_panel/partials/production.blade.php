@extends('awf-extension::display.shift_management_panel.shift_management_base')

@section('awf-shift-content')
    <style>
        .even {
            background-color: #647ed5;
        }

        .odd {
            background-color: #bbbdc2;
        }
    </style>
    <table style="margin-top: 15vh; margin-left: 20vh; width: 80%; text-align: center; font-size: large; background-color: white;">
        <thead>
        <tr>
            <th></th>
            <th>Porsche termék kód</th>
            <th>Porsche szekvencia szám</th>
            <th>Oszlop</th>
            <th>Oldal</th>
            <th>Termék</th>
            <th>Szín</th>
            <th>Anyag</th>
        </tr>
        </thead>
        <tbody>
        @php($i = 1)
        @foreach($data as $workCenter => $values)
            <tr class="{{ $i % 2 == 0 ? 'odd': 'even' }}">
                <th>{{ $workCenter }}</th>
                @if(empty($values))
                    <td colspan="7"></td>
                @else
                    <td>{{ $values['porscheProduct'] }}</td>
                    <td>{{ $values['porscheSequence'] }}</td>
                    <td>{{ $values['pillar'] }}</td>
                    <td>{{ $values['side'] }}</td>
                    <td>{{ $values['product'] }}</td>
                    <td>
                        <div style="width: 3vh; height: 3vh; border-radius: 50%; background-color: {{ '#' . $values['productColor'] }}; margin-left: 30%;"></div>

                    </td>
                    <td>{{ $values['productMaterial'] }}</td>
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
@endsection
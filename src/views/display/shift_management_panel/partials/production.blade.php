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

    <div class="production-container">
        <h2 class="production-head">Responsive Tables Using LI <small>Triggers on 767px</small></h2>
        <ul class="responsive-table">
            <li class="table-header">
                <div class="col col-1">Porsche termék kód</div>
                <div class="col col-2">Porsche szekvencia szám</div>
                <div class="col col-3">Oszlop</div>
                <div class="col col-4">Oldal</div>
                <div class="col col-5">Termék</div>
                <div class="col col-6">Szín</div>
                <div class="col col-7">Anyag</div>
            </li>
            @foreach($data as $workCenter => $values)
                @if(!empty($values))
                    <li class="table-row">
                        <div class="col col-1" data-label="Porsche termék kód">{{ $values['porscheProduct'] }}</div>
                        <div class="col col-2" data-label="Porsche szekvencia szám">{{ $values['porscheSequence'] }}</div>
                        <div class="col col-3" data-label="Oszlop">{{ $values['pillar'] }}</div>
                        <div class="col col-4" data-label="Oldal">{{ $values['side'] }}</div>
                        <div class="col col-5" data-label="Termék">{{ $values['product'] }}</div>
                        <div class="col col-6" data-label="Szín">
                            <div style="width: 3vh; height: 3vh; border-radius: 50%; background-color: {{ '#' . $values['productColor'] }}; margin-left: 30%;"></div>
                        </div>
                        <div class="col col-7" data-label="Anyag">{{ $values['productMaterial'] }}</div>
                    </li>
                @endif
            @endforeach
        </ul>
    </div>


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
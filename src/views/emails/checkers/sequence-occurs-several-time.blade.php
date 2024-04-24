@if($sequencesPillar !== null && array_key_exists(0, $sequencesPillar) && !empty($sequencesPillar[0]))
    <table>
        <thead>
        <tr>
            <th>Porsche order number</th>
            <th>Porsche SEQ</th>
            <th>Pillar</th>
        </tr>
        </thead>
        <tbody>
        @foreach($sequencesPillar as $sequence)
            <tr>
                <td>{{ $sequence->SEPONR }}</td>
                <td>{{ $sequence->SEPSEQ }}</td>
                <td>{{ $sequence->SEPILL }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif

@if($sequencesSide !== null && array_key_exists(0, $sequencesSide) && !empty($sequencesSide[0]))
    <table>
        <thead>
        <tr>
            <th>Porsche order number</th>
            <th>Porsche SEQ</th>
            <th>Pillar</th>
            <th>Side</th>
        </tr>
        </thead>
        <tbody>
        @foreach($sequencesSide as $sequence)
            <tr>
                <td>{{ $sequence->SEPONR }}</td>
                <td>{{ $sequence->SEPSEQ }}</td>
                <td>{{ $sequence->SEPILL }}</td>
                <td>{{ $sequence->SESIDE }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif

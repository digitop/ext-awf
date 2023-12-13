<table>
    <thead>
    <tr>
        <th>Porsche order number</th>
        <th>Porsche SEQ</th>
        <th>Pillar</th>
        <th>Not found side</th>
    </tr>
    </thead>
    <tbody>
    @foreach($sequences as $sequence)
        @php
            $side = \AWF\Extension\Models\AWF_SEQUENCE::where([
                ['SEPONR', '=', $sequence->SEPONR],
                ['SEPSEQ', '=', $sequence->SEPSEQ],
                ['SEPSEQ', '=', $sequence->SEPONR]
                ])->first();

            if ($side->SESIDE === 'L') {
                $side = 'R';
            }

            if ($side->SESIDE === 'R') {
                $side = 'L';
            }
        @endphp
        <tr>
            <td>{{ $sequence->SEPONR }}</td>
            <td>{{ $sequence->SEPSEQ }}</td>
            <td>{{ $sequence->SEPILL }}</td>
            <td>{{ $side }}</td>
        </tr>
    @endforeach
    </tbody>
</table>


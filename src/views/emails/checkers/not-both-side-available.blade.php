<table>
    <thead>
    <tr>
        <th>Porsche order number</th>
        <th>Porsche SEQ</th>
        <th>Available Pillar</th>
    </tr>
    </thead>
    <tbody>
    @foreach($sequences as $sequence)
        <tr>
            <td>{{ $sequence->SEPONR }}</td>
            <td>{{ $sequence->SEPSEQ }}</td>
            <td>{{ $sequence->SEPILL }}</td>
        </tr>
    @endforeach
    </tbody>
</table>


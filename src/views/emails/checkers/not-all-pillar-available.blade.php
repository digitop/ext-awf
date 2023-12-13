<table>
    <thead>
    <tr>
        <th>Porsche order number</th>
        <th>Porsche SEQ</th>
    </tr>
    </thead>
    <tbody>
    @foreach($sequences as $sequence)
        <tr>
            <td>{{ $sequence->SEPONR }}</td>
            <td>{{ $sequence->SEPSEQ }}</td>
        </tr>
    @endforeach
    </tbody>
</table>


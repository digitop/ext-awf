@extends('awf-extension::display.shift_management_panel.shift_management_base')

@section('awf-shift-content')
    <div class="table-content">
        {{ $dataTable->table() }}
        {{ $dataTable->scripts() }}
    </div>
    <div class="footer">
        <a href="{{ route('awf-shift-management-panel.manual-data-record.show', ['WCSHNA' => $workCenterId]) }}"
           class="back"
        >
            {{ __('display.button.back') }}
        </a>
    </div>
@endsection

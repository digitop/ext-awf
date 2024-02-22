<?php

namespace AWF\Extension\Controllers\Web\ShiftManagement;

use App\Http\Controllers\Controller;
use AWF\Extension\Helpers\DataTable\ShiftStartDataTable;
use AWF\Extension\Helpers\Facades\Controllers\Web\ShiftManagement\ShiftManagementShiftStartPanelFacade;
use AWF\Extension\Interfaces\WebControllerFacadeInterface;
use Illuminate\Contracts\Foundation\Application as ContractsApplication;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View as IlluminateView;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App;

class ShiftManagementShiftStartPanelController extends Controller
{
    protected WebControllerFacadeInterface $facade;

    public function __construct()
    {
        $this->facade = new ShiftManagementShiftStartPanelFacade();

        if (session() === null || empty(Session::get('locale')[0])) {
            $locale = 'hu_HU';
            setlocale(LC_ALL, implode('-', explode('_', $locale)));
            Session::put('locale', $locale);
            App::setLocale(substr($locale, 0, 2));
        }
    }
    public function index(
        ShiftStartDataTable $dataTable,
        string $pillar
    ): Application|Factory|View|IlluminateView|JsonResponse|ContractsApplication|null
    {
        return $this->facade->index($dataTable, $pillar);
    }

    public function create(Request|FormRequest|null $request = null,
                           Model|string|null        $model = null
    ): Application|Factory|View|IlluminateView|ContractsApplication|null
    {
        return $this->facade->create($request, $model);
    }

    public function default(): JsonResponse
    {
        return $this->facade->default();
    }
}

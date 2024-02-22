<?php

namespace AWF\Extension\Controllers\Web;

use App\Http\Controllers\Controller;
use AWF\Extension\Helpers\Facades\Controllers\Web\WelderPanelFacade;
use AWF\Extension\Interfaces\WebControllerFacadeInterface;
use Illuminate\Contracts\Foundation\Application as ContractsApplication;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\View\View as IlluminateView;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\WORKCENTER;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App;

class WelderPanelController extends Controller
{
    protected WebControllerFacadeInterface $facade;

    public function __construct()
    {
        $this->facade = new WelderPanelFacade();

        if (session() === null || empty(Session::get('locale')[0])) {
            $locale = 'hu_HU';
            setlocale(LC_ALL, implode('-', explode('_', $locale)));
            Session::put('locale', $locale);
            App::setLocale(substr($locale, 0, 2));
        }
    }
    public function create(Request $request, string $WCSHNA): Application|Factory|View|IlluminateView|ContractsApplication|null
    {
        return $this->facade->create($request, WORKCENTER::where('WCSHNA', '=', $WCSHNA)->first());
    }
}

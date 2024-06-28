<?php

namespace AWF\Extension\Controllers\Web\Reports;

use App\Http\Controllers\Controller;
use AWF\Extension\Helpers\Facades\Controllers\Web\Reports\ProductionDetailsFacade;
use AWF\Extension\Interfaces\WebControllerFacadeInterface;
use AWF\Extension\Requests\Web\Reports\ProductionDetailsShowRequest;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App;
use Illuminate\View\View;

class ProductionDetailsController extends Controller
{
    protected WebControllerFacadeInterface $facade;

    public function __construct()
    {
        $this->facade = new ProductionDetailsFacade();

        if (session() === null || empty(Session::get('locale')[0])) {
            $locale = 'hu_HU';
            setlocale(LC_ALL, implode('-', explode('_', $locale)));
            Session::put('locale', $locale);
            App::setLocale(substr($locale, 0, 2));
        }
    }
    /**
     * @return Factory|View
     */
    public function index(): Factory|View
    {
        return $this->facade->index();
    }

    /**
     * @param ProductionDetailsShowRequest $request
     * @return Factory|View
     */
    public function show(ProductionDetailsShowRequest $request): Factory|View
    {
        return $this->facade->show($request, null);
    }
}

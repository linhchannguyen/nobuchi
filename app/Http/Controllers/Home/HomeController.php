<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\Services\Home\HomeServiceContract;

class HomeController extends Controller
{
    /**
     * class home controller
     * class controll the data and show data in view
     * @author Dat
     * date 2019/10/03
     */
    private $homeService;
    public function __construct(HomeServiceContract $homeService)
    {
        $this->homeService = $homeService;
    }
    /**
     * function index
     * @author Dat
     * date 2019/10/03
     */
    public function index(Request $request)
    {
        if(auth()->user()->type == 2)
        {
            return redirect('supplier');
        }
        $this->data['data'] = $this->homeService->turnOver();
        $this->data['ranking'] = $this->homeService->rankingOrder($request);
        $this->data['title'] = 'ECサイト運用側トップ';
        $this->data['active'] = 0;
        return view('home.home', $this->data);
    }
}

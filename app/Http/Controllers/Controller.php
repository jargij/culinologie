<?php namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesCommands;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Auth;

abstract class Controller extends BaseController
{
    use DispatchesCommands, ValidatesRequests;

    protected static $default_language = 'nl';
    
    /**
     * Controller constructor.
     */
    public function __construct()
    {
        view()->share('user', Auth::user());
    }
}

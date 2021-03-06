<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Cookbook;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Auth;
use Input;
use Session;

class CookbooksController extends Controller
{

    private static $per_page = 20;

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $cookbooks = Cookbook::with('owner')
            ->orderBy('title')
            ->orderBy('id')
            ->paginate(self::$per_page);

        return view('cookbooks.index')
            ->withCookbooks($cookbooks);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $title = Input::get('title');

        $cookbook = new Cookbook([
            'title' => $title,
            'slug' => Str::slug($title),
        ]);
        $cookbook->user_id = Auth::user()->id;

        $this->validate($request, [
            'title' => 'required|max:255',
        ]);

        if ($cookbook->save()) {
            return redirect()->route('cookbooks.index')->with('status', 'Kookboek aangemaakt.');
        } else {
            abort(500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}

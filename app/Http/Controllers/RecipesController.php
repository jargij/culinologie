<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redirect;
use Input;
use Session;
use Auth;
use DB;
use Image;
use Request as RequestFacade;
use App\Models\Recipe, App\Models\Ingredient;
use App\Helper\RecipeSearch;
use App\Models\Comment;


class RecipesController extends Controller
{
    private $db;

    private static $per_page = 25;

    public function __construct(DatabaseManager $db)
    {
        parent::__construct();
        $this->db = $db;
    }

    /**
     * Display a listing of the resource.
     *
     * @param string $cookbook
     * @return Response
     */
    public function index($cookbook = '*')
    {
        $languages = Input::get('lang', ['nl', 'uk']);
        $search = new RecipeSearch();
        $search->setCookbook($cookbook);

        $recipes = $search->buildQuery()
            ->select('tracking_nr', 'title', 'category', 'cookbook', 'language')
            ->whereIn('language', $languages)
            ->orderBy('created_at', 'desc');
        $count = $recipes->count();
        $url_params = array_merge($search->getParams(), [
            'lang[]' => $languages,
        ]);
        $recipes = $recipes->paginate(static::$per_page)
            ->appends($url_params);

        Session::flash('return_url', route('recipes.index', $search->getParams()));

        $available_languages = $search->buildQuery()
            ->select('language')->distinct()
            ->orderBy('language', 'desc')
            ->get()->lists('language')->all();

        return view('recipes.index')
            ->with('recipes', $recipes)
            ->with('count', $count)
            ->with('chosen_languages', $languages)
            ->with('available_languages', $available_languages)
            ->with('categories', Recipe::categories($languages))
            ->with('hide_cookbooks', $search->shouldHideCookbooks())
            ->with('params', $search->getParams());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return view('recipes.create')->withRecipe(new Recipe);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store($cookbook = null)
    {
        $recipe = new Recipe();
        $recipe->user_id = Auth::user()->id;

        if ($cookbook != null) {
            $recipe->cookbook = $cookbook;
        }

        return $this->saveRecipe($recipe);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $recipes = Recipe::where('tracking_nr', '=', $id)
            ->orderBy('language', 'asc')->get();

        $language = Input::get('lang', null);
        $recipe = false;
        foreach ($recipes as $r) {
            if ($r->language == $language) {
                $recipe = $r;
            } elseif ($language == null) {
                $recipe = $r;
                break;
            }
        }

        if (!$recipe) {
            abort(404);
        }

        $ingredients = $recipe->ingredients->groupBy('header');
        
        return view('recipes.show')
            ->with('recipe', $recipe)
            ->with('recipes', $recipes)
            ->with('cookbook', $recipe->cookbook_rel)
            ->with('ingredients', $ingredients);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $lang = Input::get('lang', static::$default_language);
        $recipe = Recipe::where('tracking_nr', '=', $id)
            ->where('language', '=', $lang)
            ->first();

        if (!$recipe) {
            abort(404);
        }

        return view('recipes.create')
            ->with('recipe', $recipe);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $tracking_nr
     * @return Response
     */
    public function update($tracking_nr)
    {
        $lang = Input::get('lang');
        $recipe = Recipe::where('tracking_nr', '=', $tracking_nr)
            ->where('language', '=', $lang)
            ->first();

        if (!$recipe) {
            abort(404);
        }

        $this->db->table('ingredients')
            ->where('recipe_id', '=', $recipe->id)->delete();

        return $this->saveRecipe($recipe);
    }

    private function saveRecipe(Recipe $recipe)
    {
        $input = Input::only('title', 'people', 'presentation', 'year', 'season',
            'cookbook', 'category', 'temperature', 'visibility', 'tracking_nr'
        );

        $recipe->fill($input);

        if (empty($recipe->tracking_nr)) {
            $recipe->tracking_nr = $this->db->table('recipes')->max('tracking_nr') + 1;
        }

        if (Input::has('lang')) {
            $recipe->language = Input::get('lang');
        }

        if (Input::has('directions')) {
            $recipe->description = htmlentities(Input::get('directions'));
        }

        // Override the category if the user provided one.
        if (!empty(Input::get('category_alt'))) {
            $recipe->category = Input::get('category_alt');
        }

        try {
            $recipe_saved = $recipe->save();
        } catch (QueryException $e) {
            $recipe->tracking_nr = $this->db->table('recipes')->max('tracking_nr') + 1;
            $recipe_saved = $recipe->save();
            Session::flash('warning', 'Let op: je recept is onder een nieuw volgnummer bewaard, omdat het opgegeven nummer al in gebruik was.');
        }

        if (RequestFacade::hasFile('picture')) {
            $path = join(DIRECTORY_SEPARATOR, [
                public_path(),
                'uploads',
                'pictures'
            ]);
            $filename = $recipe->tracking_nr.'.jpg';
            $file = RequestFacade::file('picture')->move($path, $filename);
            $image = Image::make($file)->widen(480);
            $image->save($path.DIRECTORY_SEPARATOR.$filename);
        }

        $ingredients_saved = $recipe->saveIngredientsFromText(Input::get('ingredients'));

        if ($recipe_saved && $ingredients_saved) {
            return redirect()->route('recipes.show', ['recipes' => $recipe->tracking_nr])->with('lang', $recipe->language);
        } else {
            abort(500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id, Request $request)
    {
        if (!Input::has('lang')) {
            return abort(500);
        }

        $recipe = Recipe::where('tracking_nr', '=', $id)
            ->where('language', '=', Input::get('lang'))
            ->first();

        if (!Auth::check()) {
            return abort(401);
        }
        if ($recipe->cookbook_rel->user_id != $request->user()->id) {
            return abort(401);
        }

        if ($recipe->delete()) {
            return redirect()->route('recipes.index')
                ->with('status', 'Recept verwijderd.');
        } else {
            return abort(500);
        }
    }

    public function fork($tracking_nr)
    {
        $lang = Input::get('lang', static::$default_language);
        $recipe = Recipe::where('tracking_nr', '=', $tracking_nr)
            ->where('language', '=', $lang)
            ->first();

        if (!$recipe) {
            abort(404);
        }

        $new_recipe = $recipe->replicate();
        // We can do this since the ingredients are converted to text anyways.
        $new_recipe->ingredients = $recipe->ingredients;

        return view('recipes.create')
            ->with('recipe', $new_recipe);
    }

    public function random()
    {
        $language = Input::get('lang', static::$default_language);

        $recipe = Recipe::select('tracking_nr', 'language')
            ->where('language', $language)
            ->orderByRaw('RAND()')
            ->first();

        return redirect()->route('recipes.show', ['recipes' => $recipe->tracking_nr])
            ->with('lang', $recipe->language);
    }
}

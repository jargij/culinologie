<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use DB, Auth;

class BookmarksController extends Controller
{
    const DEFAULT_LIST = 'Loved';

    public function store(Request $request, $tracking_nr)
    {
        $list = static::DEFAULT_LIST;
        $language = $request->get('language', static::$default_language);

        $recipe = Recipe::select('id')->where('tracking_nr', '=', $tracking_nr)
            ->where('language', '=', $language)
            ->first();

        if (!$recipe) abort(500);

        DB::table('recipe_bookmarks')->insert([
            'user_id' => Auth::user()->id,
            'list' => $list,
            'recipe_id' => $recipe['id']
        ]);

        return redirect()->route('recipes.show', ['recipes' => $tracking_nr])
            ->with('lang', $language);
    }

    public function destroy(Request $request, $tracking_nr)
    {
        $list = static::DEFAULT_LIST;
        $language = $request->get('language', static::$default_language);

        $recipe = Recipe::select('id')->where('tracking_nr', '=', $tracking_nr)
            ->where('language', '=', $language)
            ->first();

        if (!$recipe) abort(500);

        DB::table('recipe_bookmarks')
            ->where('recipe_id', $recipe['id'])
            ->where('user_id', Auth::user()->id)
            ->where('list', $list)
            ->delete();

        return redirect()->route('recipes.show', ['recipes' => $tracking_nr])
            ->with('lang', $language);
    }
}

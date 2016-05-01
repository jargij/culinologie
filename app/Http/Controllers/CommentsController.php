<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Auth;

class CommentsController extends Controller
{
    public function store(Request $request, $tracking_nr)
    {
        $comment = new Comment($request->all());
        $comment->user_id = Auth::user()->getKey();
        $comment->recipe_tracking_nr = $tracking_nr;
        $comment->save();

        $language = $request->get('language', static::$default_language);
        return redirect()->route('recipes.show', ['recipes' => $tracking_nr])
            ->with('lang', $language);
    }

    public function destroy($tracking_nr, $comment_id)
    {
        /** @var Comment $comment */
        $comment = Comment::findOrFail($comment_id);
        if ($comment->user_id == Auth::user()->id) {
            $comment->delete();
            return redirect()->back()->with('status', 'Review is verwijderd!');
        }
        else {
            return redirect()->back()->with('status', 'Review kan niet verwijderd worden!');
        }
    }
}

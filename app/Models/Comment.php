<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class Comment extends Model
{

    protected $fillable = ['title', 'body', 'user_id','recipe_id','rating'];

    public $timestamps = true;

    public function author()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
    
    public function recipe()
    {
        return $this->belongsTo('App\Models\Recipe', 'recipe_tracking_nr', 'tracking_nr');
    }

    public function setBodyAttribute($newValue) {
        $this->attributes['body'] = htmlentities($newValue);
    }
    
    public function getHtmlStars()
    {
        return html_rating($this->rating);
    }
}

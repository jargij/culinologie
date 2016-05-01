@extends('app')

@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-3 hidden-print">
            @if(Session::has('return_url'))
            <p>
                <a href="{{ Session::get('return_url') }}" class="btn btn-default">&larr; Terug</a>
            </p>
            @endif

            <p>
                <a href="/recipes/{{ $recipe->tracking_nr }}/edit?lang={{ $recipe->language }}" class="btn btn-success"><i class="fa fa-edit"></i> Bewerken</a>
                <a href="/recipes/{{ $recipe->tracking_nr }}/fork?lang={{ $recipe->language }}" class="btn btn-default"><i class="fa fa-copy"></i> Kopi&euml;ren</a>

                @if(Auth::check())
                    <form method="post" action="/recipes/{{ $recipe->tracking_nr }}/{{ $user->hasLovedRecipe($recipe) ? 'unbookmark' : 'bookmark' }}">
                        <input type="hidden" name="language" value="{{ $recipe->language }}" />
                        {!! csrf_field() !!}

                        @if($user->hasLovedRecipe($recipe))
                        <button type="submit" class="btn btn-default active"><i class="fa fa-heart"></i> Bewaren</button>
                        @else
                        <button type="submit" class="btn btn-default"><i class="fa fa-heart-o"></i> Bewaren</button>
                        @endif
                    </form>
                @endif
            </p>
        </div>

        <div class="col-md-6">
            
            <h1>{{ $recipe->title }} <small class="pull-right">{!!$recipe->getRating('html_stars')!!} <small><a href="#comments">({{$recipe->getRating('count')}})</a></small></small></h1>

            @if($recipe->people != 0)
            <p>Voor {{ $recipe->people }} personen.</p>
            @endif
        </div>


        @if(count($recipes) > 1)
        <div class="col-md-3 hidden-print">
            <form class="form-inline" style="display: block;" action="/recipes/{{ $recipe->tracking_nr }}">
                <h4>Taal</h4>
                <div class="form-group">
                    <select name="lang" class="form-control">
                        @foreach($recipes as $r)
                            <option value="{{ $r->language }}" {{ $r->language == $recipe->language ? 'selected' : '' }}>
                                {{ $languages[$r->language] or $r->language }}</option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn btn-primary">Ga</button>
                </div>
            </form>
        </div>
        @endif

    </div>

    <div class="row">
        <div class="col-md-3">
            <h2>Ingrediënten</h2>

            @foreach($ingredients as $title => $group)
                <h4>{{ $title }}</h4>
                <ul>
                @foreach($group as $in)
                    <li>{{ $in->text }}</li>
                @endforeach
                </ul>
            @endforeach

            <h2>Informatie</h2>

            <table class="table">
                <tr>
                    <th>Kookboek</th>
                    <td><a href="/cookbooks/{{ $cookbook->slug }}/recipes">{{ $cookbook->title }}</a></td>
                </tr>
                <tr>
                    <th>Categorie</th>
                    <td><a href="/recipes/?category={{ $recipe->category }}">{{ $recipe->category }}</a></td>
                </tr>
                <tr>
                    <th>Temperatuur</th>
                    <td>{{ $temperatures[$recipe->temperature] or $recipe->temperature }}</td>
                </tr>
                <tr>
                    <th>Seizoen</th>
                    <td>{{ $seasons[$recipe->season] or $recipe->season }}</td>
                </tr>
                <tr>
                    <th>Jaar</th>
                    <td>{{ $recipe->year }}</td>
                </tr>
                <tr>
                    <th>Zichtbaar</th>
                    <td>{{ $visibilities[$recipe->visibility] }}</td>
                </tr>
                <tr>
                    <th>Toegevoegd</th>
                    <td>{{ $recipe->created_at->format('d M Y, H:i') }}</td>
                </tr>
                <tr>
                    <th>Laatst gewijzigd</th>
                    <td>{{ $recipe->updated_at->format('d M Y, H:i') }}</td>
                </tr>
            </table>

        </div>

        <div class="col-md-6">
            <h2>Bereiding</h2>

            {!! $recipe->getHtmlDescription() !!}

            @if(!empty($recipe->presentation))
            <h2>Finishing touches</h2>

            {!! $recipe->getHtmlPresentation() !!}
            @endif
           
        </div>

        <div class="col-md-3 sidebar">
            @if(file_exists(public_path().key($recipe->getImages())))
                <h4>Foto&#39;s</h4>
            @endif
            @foreach($recipe->getImages() as $url => $title)
                @if(file_exists(public_path().$url))
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <img src="{{ $url }}" alt="{{ $title }}" />
                        </div>
                        <div class="panel-footer">{{ $title }}</div>
                    </div>
                @endif
            @endforeach
        </div>
            
        <div class="col-md-9" id="comments">
            <h3 class="text-left">Reacties
                @if(Auth::check())
                    <button onClick="$('#reviewForm').toggleClass('hidden');" class="btn btn-primary pull-right btn-sm">Reactie formulier</button>
                @endif
            </h3>
            @if(Auth::check())
            <form class="form-horizontal hidden" id="reviewForm" method="post" action="{{route('recipes.postComment', $recipe->tracking_nr)}}">
                <fieldset>
                {!! csrf_field() !!}
                <!-- Form Name -->
                <legend>Laat een reactie achter!</legend>
                
                <!-- Text input-->
                <div class="form-group">
                  <label class="col-md-2 control-label" for="title">Titel*</label>  
                  <div class="col-md-8">
                  <input id="title" name="title" type="text" placeholder="Titel van je review" class="form-control input-md" required="">
                    
                  </div>
                </div>
                
                <!-- Multiple Radios (inline) -->
                <div class="form-group">
                  <label class="col-md-2 control-label" for="rating">Rating
                      <small style="margin-left: 10px;"><a class="btn btn-xs btn-primary" id="rating-switch">On</a></small>
                  </label>
                  <div class="col-md-5">
                    <label class="radio-inline" for="rating-0">
                      <input type="radio" name="rating" id="rating-0" value="1">
                      1
                    </label> 
                    <label class="radio-inline" for="rating-1">
                      <input type="radio" name="rating" id="rating-1" value="2">
                      2
                    </label> 
                    <label class="radio-inline" for="rating-2">
                      <input type="radio" name="rating" id="rating-2" value="3">
                      3
                    </label> 
                    <label class="radio-inline" for="rating-3">
                      <input type="radio" name="rating" id="rating-3" value="4">
                      4
                    </label> 
                    <label class="radio-inline" for="rating-4">
                      <input type="radio" name="rating" id="rating-4" value="5">
                      5
                    </label>
                  </div>

                </div>
                
                <!-- Textarea -->
                <div class="form-group">
                  <label class="col-md-2 control-label" for="body">Bericht</label>
                  <div class="col-md-8">                     
                    <textarea class="form-control" id="body" name="body"></textarea>
                  </div>
                </div>
                
                <!-- Submit button -->
                <div class="form-group">
                  <label class="col-md-2 control-label" for="body"></label>
                  <div class="col-md-8">                     
                    <button type="submit" class="btn btn-success">Verstuur je reactie &raquo;</button>
                  </div>
                </div>
                </fieldset>
            </form>

            @endif
            <div class="row">
        		                
                <div class="list-group">
                @if($recipe->comments->count() == 0)
                <p class="text-center well">Geen reacties gevonden...</p>
                @else
                  @foreach($recipe->comments()->orderBy('id','DESC')->get() as $comment)
                  <div class="list-group-item">

                       <div class="pull-right">
                            <small><i class="fa fa-user fa-fw"></i> {{$comment->author->name}}</small>
                            <br>

                           <div class="stars text-left">
                               @if ($comment->rating != NULL)
                                {!!$comment->getHtmlStars()!!}
                               @else
                                   Geen rating
                               @endif
                            </div>

                        </div>

                        <h4 class="list-group-item-heading">
                            {{$comment->title}}
                            @if (Auth::check())
                                @if ($comment->user_id == Auth::user()->id)
                                    <a data-url="{{route('recipes.deleteComment',[$recipe->id, $comment->id])}}" class="btn btn-xs text-danger pull-right deleteComment"><i class="fa fa-trash-o fa-fw"></i></a>
                                @endif
                            @endif
                            <br>
                            <p style="font-size: 75% !important;"><small class="text-muted">{{$comment->created_at}}</small></p>

                        </h4>
                        <p class="list-group-item-text">
                            {{ $comment->body }}
                        </p>

                    </div>
                   @endforeach
                @endif
                </div>
            </div>
        </div>


    </div>
</div>
@endsection

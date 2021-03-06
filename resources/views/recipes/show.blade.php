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
            <h1>{{ $recipe->title }}</h1>

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
    </div>
</div>

@endsection

@extends('layouts.front')

@include('partials.meta')

@section('content')
    <!-- Panel -->
    <div class="mt-4">{{ Breadcrumbs::render('post', $post->title) }}</div>
    <div class="page-blog-content mt-25 card">
        <div class="post-rating post-rating-show">

            @include('elements.panel-left')

        </div>
        <div class="panel-list">
            <div class="container-fluid">
                <div class="email-title">
                    <div class="d-flex">
                        <input type="hidden" id="post_id" value="{{$post->id}}">
                        @if($post->author->userinfo == null)
                            <div class="avatar-user">
                        @elseif($post->author->userinfo->type == 1)
                            <div class="avatar-company">
                        @else
                            <div class="avatar-user">
                        @endif
                                <a href="{{ route('profile.show', $post->author->id) }}"><img
                                    src="{{$post->author->getAvatar()}}"
                                    alt="{{$post->author->name}} {{$post->author->lastname}}"
                                    style="margin-top: 3px;"></a>
                            </div>
                        <div class="ml-3"><a class="px-10 corp-color"
                                             href="{{ route('profile.show', $post->author->id) }}">{{$post->author->name}} {{$post->author->lastname}}</a>
                            <p>{{ $post->getHumansDate($post->created_at) }}</p></div>
                            </div>
                            </div>
                            <a class="post-title" href="{{ route('post.show', $post->slug) }}">
                                <h1 class="card-title text-dark">{{ $post->title }}</h1>
                            </a>
                        <span>
                          @foreach($post->getCategories() as $category)
                            <div class="d-inline-flex pb">
                              <span class="badge badge-secondary">
                                <a class="category-link text-decoration-none" href="{{ route('category.show', $category->slug) }}">
                                  {{$category->title}}
                                </a>
                              </span>
                            </div>
                          @endforeach
                        </span>
                    </div>
                    <div class="raz"></div>
                    <div class=" px-15 border-0 mb-5 pb-5">
                        <div class="card-block px-0">
                            <div class="card mb-0 border-0">
                                <div class=" comment-border">
                                    <div class="card-header cover overlay p-0 border-0">
                                        @if($post->getImage())
                                            @if(env('IS_LOCAL')==true)
                                                <img class="cover-image" src="{{ secure_asset($post->getImage()) }}"
                                                     alt="...">
                                            @else
                                                <img class="cover-image" src="{{ $post->getImage() }}" alt="...">
                                            @endif
                                        @endif
                                        <div class="overlay-panel"></div>
                                    </div>
                                    <div class="card-block px-4">
                                        <div class="entry-content mt-3">{!! $post->getText($post->content) !!}</div>
                                        <span class="tags">
                <p class="card-text">
                  <b>Теги:</b>
                  @foreach($post->getTags() as $tag)
                        <a class="orange" href="{{ route('tag.show', $tag->id) }}">
                      @if($loop->iteration == $loop->count )
                                {{$tag->title}}
                            @else
                                {{$tag->title}},
                            @endif
                    </a>
                    @endforeach
                </p>
              </span>
                                    </div>
                                    <div
                                        class="card-block px-4 pb-0 mt-2 mb-5 d-flex justify-content-center justify-content-md-start"
                                        id="place_comments">
                                        @include('elements.panelpost')
                                        {{--route(blog) to share buttons--}}

                                    </div>
                                </div>
                                <div class="card-block px-4">
                                        @if($post->getCountComments() == 0 && Auth::user())
                <h3  id="none-comment" class="card-heading pb-2">
                  <p>Комментариев нет</p>
                </h3>
                  <ol style="list-style-type: none;" class="pl-0 pt-20" id="all-comment">
                  </ol>
                                        @elseif($post->getCountComments() == 0 && !Auth::user())
                                            <ol style="list-style-type: none;" class="pl-0 pt-20" id="all-comment">
                                            </ol>
                                        @endif
                                    @foreach($comments as $parent => $com)
                                        @if($parent)
                                            @break
                                        @endif
                                        <ol style="list-style-type: none;" id="all-comment" class="pl-0 pt-20">
                                            @include('comment.show', ['items' => $com])
                                        </ol>
                                    @endforeach
                                </div>
                                <div class="col-md-12 px-4">
                                    @include('comment.response')
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="response-area"></div>
@stop

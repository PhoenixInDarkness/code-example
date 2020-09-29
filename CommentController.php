<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\Blog\Post;
use App\Traits\HelpTraits;
use App\Models\Blog\Comment;
use Illuminate\Http\Request;

class CommentsController extends Controller
{
    use HelpTraits;

    /**
     * @param Request $request
     * @return \Illuminate\View\View|string
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request->text = $this->textTrim($request->text);

        $text = substr($request->text, strpos($request->text, '</a>,&nbsp;')+11);

        if($text !== false){
            $is_text = empty(trim($text));

            if($is_text == true) return $this->returnIfNonText();
        }

        $text_is = empty($request->text);

        if($text_is){
            return $this->returnIfNonText();
        }

        $this->validate($request, [
            'text'	=>	'required',
        ], $this->messages());


        $isset_parent = (isset($request->parent_id))??true;
        $oComment = Comment::add($request->all());
        $oComment->setPost($request->get('post_id'));
        $oComment->setParent($request->get('parent_id'));
        $parent = ($oComment->parent_id)??$oComment->id;
        $view = view('comment.body', ['item' => $oComment, 'child' => $isset_parent, 'parent' => $parent]);

        if(!$isset_parent)
        {
            $result = '<li style="list-style-type: none;" id="response-' . $oComment->id . '">' . $view . '</li>';
        } else {
            $result = $view;
        }

        return $result;
    }

    /**
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function edit(Request $request)
    {
        $oComment = Comment::find($request->id);

        if($oComment->user_id == Auth::user()->id) {
            $sText = $oComment->text;
            $iCommentId = $oComment->id;
            $iPostId = $oComment->post_id;
        } else {
            return view('errors.404');
        }

        return view('comment.edit', compact('oComment'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\View\View|mixed
     */
    public function update(Request $request)
    {
        $oComment = Comment::find($request->comment_id);

        if($oComment->user_id == Auth::user()->id) {
            $oComment->text = $request->text;
            $oComment->save();
        } else {
            return view('errors.404');
        }

        return $oComment->text;
    }

    /**
     * @param $id
     * @return \Illuminate\View\View|integer
     */
    public function destroy($id)
    {
        $oComment = Comment::find($id);

        if($oComment->user_id == Auth::user()->id) {
            $oComment->deleted = 1;
            $oComment->save();
        } else {
            return view('errors.404');
        }

        return $oComment->id;
    }

    /**
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function response(Request $request)
    {
        $comment_id = $request->comment_id;
        $name = $request->name;
        $post_id = $request->post_id;

        return view('comment.response_panel', compact('comment_id', 'name', 'post_id'));
    }

    /**
     * @return array()
     */
    public function messages(){
        $messages = array(
            'text.required' => 'Вы не написали текст комментария',
        );

        return $messages;
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function returnIfNonText(){
        $error = array('message' => 'The given data was invalid.',
                  'errors' => array('text' =>['Вы не написали текст комментария.'])
              );

        return response()->json($error, 422);
    }

}

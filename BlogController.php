<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Blog\Post;
use App\Models\Blog\Tag;
use App\Models\Blog\MetaTag;
use App\Traits\HelpTraits;
use App\User;
use Auth;
use DB;
use Route;

class BlogController extends Controller
{
    use HelpTraits;

    /**
     * Главная страница
     *
     * @return \Illuminate\View\View
     */
	public function index()
	{
		$oMeta = new MetaTag;
        $aMeta = array();
        $aMeta = $this->prepareMetaInfo($aMeta, 'title', 'Последние публикации / HSE');
        $aMeta = $this->prepareMetaInfo($aMeta, 'description', 'Последние публикации на hseblog');
		$oMeta = $oMeta->set($aMeta);
		$oPosts = Post::where('is_sandbox', 0)->orderBy('created_at', 'desc')->paginate(10);

		return view('posts.index', compact('oPosts', 'oMeta'));
	}

    /**
     * Добавить в закладки
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
	public function bookmarkAdd(Request $request)
	{
		if(!Auth::user()){
			$aResponse = array('status' => false);
			return response()->json($aResponse);
			exit;
		}
		$oPost = Post::find($request->post_id);
		$getPost = $oPost->checkBookmark($request->post_id);


		if($getPost == false){
            $oPost->removeBookmark(Auth::user()->id);
		} else {

            $oPost->setBookmark(Auth::user()->id);
		}

		$iCount = $oPost->countBookmark();

        $aResponse = array(
			'status' => true,
			'count' => $iCount,
		);

		return response()->json($aResponse);
	}

    /**
     * Список авторов
     *
     * @return \Illuminate\View\View
     */
	public function users(){
		$oUsers = User::where('role_id', 6)->paginate(10);
		$sTitle = "Список авторов";

		return view('users', compact('oUsers', 'sTitle'));
	}

    /**
     * Добавить/удалить подписку
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
	public function subscriptionsCreate(Request $request)
	{
		$oUser = Auth::user();
		$bCheck = $oUser->checkSub($request->author_id);

		if(!$bCheck){
            $oUser->subscriptions()->detach($request->author_id);
		} else {
            $oUser->subscriptions()->attach($request->author_id);
		}

		$aResponse = array(
			'author_id' => $request->author_id,
			);

		return response()->json($aResponse);
	}

    /**
     * Получить теги
     *
     * @param Request $request
     * @return array()
     */
	public function tag(Request $request)
	{
		$sResult = "";
		$aTags = explode(', ', $request->tags);
		$iCount = count($aTags) - 1;
		$tagR = $aTags["$iCount"];
		$oTag = Tag::where('title', 'like','%' . $tagR . '%')->limit(1)->pluck('title');

		if($oTag->isEmpty()){ die(); }
		$aArray  = $aTags;
        $aArray["$iCount"] = $oTag[0];
		foreach ($aArray as $id => $arr) {
			if($id == $iCount){
                $sResult .= $arr;
			} else {
                $sResult .= $arr . ', ';
			}
		}

		return $sResult;
	}

}

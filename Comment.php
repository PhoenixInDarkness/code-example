<?php

namespace App\Models\Blog;

use Auth;
use App\User;
use App\Traits\HelpTraits;
use Wkhooy\ObsceneCensorRus;
use Illuminate\Database\Eloquent\Model;
use Laravelrus\LocalizedCarbon\LocalizedCarbon;
use Laravelrus\LocalizedCarbon\Traits\LocalizedEloquentTrait;

class Comment extends Model
{
    use HelpTraits;

    protected $fillable = ['text'];

    public function post()
    {
    	return $this->belongsTo(Post::class);
    }

    public function author()
    {
    	return $this->belongsTo(User::class, 'user_id');
    }

    public function likes()
    {
    	return $this->morphMany(Like::class, 'entity');
    }

    public static function add($fields)
    {

        $content = $fields['text'];
        $sCensorshipContent = ObsceneCensorRus::getFiltered($content);
        $fields['text'] = $sCensorshipContent;

        $comment = new static;
        $comment->fill($fields);
        $comment->text = $comment->textTrim($fields['text']);
        $comment->user_id = Auth::user()->id;
        $comment->save();

        return $comment;
    }

    public function remove()
    {
        $this->delete();
    }

    public function setPost($id)
    {
        if($id == null) {return;}
        $this->post_id = $id;
        $this->save();
    }

    public function setParent($id)
    {
        if($id == null) {return;}
        $this->parent_id = $id;
        $this->save();
    }

    public function getCountLikes()
    {
        $like = $this->likes()->where('active', '=', '1')->where('type', '=', '1')->count();
        $dislike = $this->likes()->where('active', '=', '1')->where('type', '=', '0')->count();
        $sum = $like - $dislike;

        return $sum;
    }

    public function getLike()
    {
        return $this->likes()->first();
    }

    public function checkLike($entity_id, $type)
    {
        $user_id = Auth::user()->id;
        $check =$this->likes()->where('entity_id', $entity_id)->where('user_id', $user_id)->where('active','1')->where('type', $type)->first();

        return $check;
    }

    public function countUserComments($id)
    {
        return $this->where('user_id', $id)->count();
    }

    public function getTitlePost()
    {
        if($this->post){
            $title = $this->post->only('title');
            $title = $title['title'];
            return $title;
        } else {
            return false;
        }
    }

    public function getHumansDate($date)
    {
        $newDate = self::getRuHumansDate($date);

        return $newDate;
    }
}

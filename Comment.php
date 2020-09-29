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

    /**
     * Отношения с моделью App\Models\Blog\Post
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function post()
    {
    	return $this->belongsTo(Post::class);
    }

    /**
     * Отношения с моделью App\Models\Blog\User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function author()
    {
    	return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Отношения с моделью App\Models\Blog\User
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function likes()
    {
    	return $this->morphMany(Like::class, 'entity');
    }

    /**
     * Добавление комментария
     *
     * @param $fields
     * @return static
     */
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

    /**
     * Удаление комментария
     */
    public function remove()
    {
        $this->delete();
    }

    /**
     * Прикрепляет комментарий к посту
     *
     * @param $id
     */
    public function setPost($id)
    {
        if($id == null) {return;}
        $this->post_id = $id;
        $this->save();
    }

    /**
     * Устанавливает родителя для комментария
     *
     * @param $id
     */
    public function setParent($id)
    {
        if($id == null) {return;}
        $this->parent_id = $id;
        $this->save();
    }

    /**
     * Возвращает результат: лайки минус дизлайки
     *
     * @return int
     */
    public function getCountLikes()
    {
        $like = $this->likes()->where('active', '=', '1')->where('type', '=', '1')->count();
        $dislike = $this->likes()->where('active', '=', '1')->where('type', '=', '0')->count();
        $sum = $like - $dislike;

        return $sum;
    }

    /**
     * Получить лайк
     *
     * @return Model|\Illuminate\Database\Eloquent\Relations\MorphMany|object|null
     */
    public function getLike()
    {
        return $this->likes()->first();
    }

    /**
     * Проверить установлен ли лайк
     *
     * @param $entity_id
     * @param $type
     * @return Model|\Illuminate\Database\Eloquent\Relations\MorphMany|object|null
     */
    public function checkLike($entity_id, $type)
    {
        $user_id = Auth::user()->id;
        $check =$this->likes()->where('entity_id', $entity_id)->where('user_id', $user_id)->where('active','1')->where('type', $type)->first();

        return $check;
    }

    /**
     * Считает количество комментариев пользователя
     *
     * @param $id
     * @return mixed
     */
    public function countUserComments($id)
    {
        return $this->where('user_id', $id)->count();
    }

    /**
     * Получить описание поста
     *
     * @return false|mixed
     */
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

    /**
     * Получить человекочитаемую дату
     * @param $date
     * @return string
     */
    public function getHumansDate($date)
    {
        $newDate = self::getRuHumansDate($date);

        return $newDate;
    }
}

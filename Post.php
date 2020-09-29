<?php

namespace App\Models\Blog;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Laravelrus\LocalizedCarbon\Traits\LocalizedEloquentTrait;
use Cviebrock\EloquentSluggable\Sluggable;
use App\Models\Blog\MetaTag;
use Wkhooy\ObsceneCensorRus;
use Illuminate\Support\Str;
use App\Traits\HelpTraits;
use App\User;
use Auth;

class Post extends Model
{
    use Sluggable, HelpTraits;

    protected $fillable = ['title','content', 'date', 'description'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function categories()
    {
    	return $this->belongsToMany(
            Category::class,
            'posts_categories',
            'post_id',
            'category_id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function author()
    {
    	return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
     public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function likes()
    {
    	return $this->morphMany(Like::class, 'entity');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function bookmark()
    {
        return $this->belongsToMany(
            User::class,
            'bookmark',
            'post_id',
            'user_id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tags()
    {
        return $this->belongsToMany(
            Tag::class,
            'posts_tags',
            'post_id',
            'tag_id'
        );
    }

    /**
     * @return array()
     */
    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function metaTags()
    {
        return $this->morphMany(MetaTag::class, 'entity');
    }

    /**
     * @param $fields
     * @return static
     */
    public static function add($fields)
    {
        $content = $fields['content'];
        $sCensorshipContent = ObsceneCensorRus::getFiltered($content);
        $fields['content'] = $sCensorshipContent;
    	$post = new static;
    	$post->fill($fields);
    	$post->user_id = Auth::user()->id;
        $post->description = mb_substr(strip_tags($post->content), 0, 500) . '...';
    	$post->save();

    	return $post;
    }

    /**
     * @param $fields
     */
    public function edit($fields)
    {
        $this->fill($fields);
        $this->description = mb_substr(strip_tags($this->content), 0, 500) . '...';
        $this->save();
    }
    
    public function remove()
    {
        $this->removeImage();
        $this->delete();
    }

    public function preRemove()
    {
        $this->deleted = 1;
        $this->updated_at = Carbon::now();
        $this->save();
    }

    public function recover()
    {
        $this->deleted = 0;
        $this->updated_at = Carbon::now();
        $this->save();
    }

    public function uploadImage($image)
    {
        if($image == null) { return; }

        $this->removeImage();

        $img = str_replace('data:image/png;base64,', '', $image);
        $img = str_replace(' ', '+', $img);
        $fileData = base64_decode($img);
        $name = Str::random(15) . '.jpg';
        // file_put_contents(public_path('storage/' . $name), $fileData);

        $image = imagecreatefromstring($fileData);
        imageinterlace($image, true);
        imagejpeg($image, public_path('storage/' . $name));
        imagedestroy($image);

        $this->image = $name;
        $this->save();
    }

    public function getImage()
    {
        if($this->image == null)
        {
            return false;
        }

        return '/storage/' . $this->image;
    }

    public function removeImage()
    {
        if($this->image != null)
        {
           $a = Storage::delete('/public/' . $this->image);
        }
    }

     public function setCategory($ids)
    {
        if($ids == null){return;}

        $this->categories()->sync($ids);
    }

    public function getCategories()
    {

        return $this->categories;
    }

    public function getCategoryTitle()
    {
        return ($this->category != null)
                ?   $this->category->title
                :   'Нет категории';
    }

     public function getCategoryID()
    {
        return $this->category != null ? $this->category->id : null;
    }

    public function getDescription()
    {
        $sNoneTags = strip_tags($this->content);
        $sLenght =  mb_substr($sNoneTags, 0, 500);
        $oDescription = $sLenght . '...';

        return $oDescription;
    }

    public function getText($text)
    {
        $text = str_replace('<p>&lt;cut&gt;</p>', '', $text);

        return $text;
    }

    public function getComments()
    {
        return $this->comments()->get();
    }

    public function getCountComments()
    {
       return $this->comments()->count();
    }

    public function getCountLikes()
    {
        $like = $this->likes()->where('active', '=', '1')->where('type', '=', '1')->count();
        $dislike = $this->likes()->where('active', '=', '1')->where('type', '=', '0')->count();
        $sum = $like - $dislike;

        return $sum;
    }

    public function getLikes()
    {
        $like = $this->likes()->first();

       return $like;
    }

    public function checkLikes($entity_id, $user_id)
    {
        $check =$this->likes()->where('entity_id', '=', $entity_id)->where('user_id', '=', $user_id)->first();

        return $check;
    }
    public function checkLike($entity_id, $type)
    {
        $user_id = Auth::user()->id;
        $check =$this->likes()->where('entity_id', $entity_id)->where('user_id', $user_id)->where('active','1')->where('type', $type)->first();

        return $check;
    }

    public function setBookmark($id)
    {

        if($id == null){return;}

        $this->bookmark()->attach($id);
    }

    public function removeBookmark($id)
    {
        if($id == null){return;}

        $this->bookmark()->detach($id);
    }

    public function checkBookmark($post_id)
    {
        $book = $this->bookmark()->where('post_id',$post_id)->where('user_id',Auth::user()->id)->get();
        $bookmark = $book->isEmpty();

        return $bookmark;
    }

    public function countBookmark()
    {
        return $this->bookmark()->count();
    }

    public function setTags($ids)
    {
        if($ids == null){return;}

        $this->tags()->sync($ids);
    }

    public function addTags($tags)
    {
        $fillTag = array();
        foreach ($tags as $tag) {
            if ($tag == null || $tag == " ") {
                break;
            }
            $tags = $this->checkTag($tag);
            $fillTag[] = $tags;
        }
        return $fillTag;
    }

    public function checkTag($tag)
    {
        $tag = ltrim($tag, " ");
        $check = Tag::where('title', $tag)->get();

        if ($check->isEmpty()) {
            $tag = ['title' => $tag];
            $tag = Tag::create($tag);
        } else {
            $tag = $check->first();
        }

        return $tag->id;
    }

    public function getTagsTitles()
    {
        return (!$this->tags->isEmpty())
            ?   implode(', ', $this->tags->pluck('title')->all())
            : 'Нет тегов';
    }

    public function getTags()
    {

        return $this->tags;
    }

    public function selectedTags()
    {
        $tagsStr = null;
        $tags = $this->tags->pluck('title')->all();
        foreach ($tags as $tag) {
            if(!$tagsStr){
                $tagsStr = "$tag" . ',';
            } else {
                $tagsStr = "$tagsStr" . " $tag" . ',';
            }
        }

        return $tagsStr;
    }

    public function getHumansDate($date)
    {
        $newDate = self::getRuHumansDate($date);

        return $newDate;
    }

    // public function getTitle($id)
    // {
    //     $this->where('id', $id)->pluck('title')->get();
    //     return
    // }
}

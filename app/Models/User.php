<?php

namespace App\Models;

use App\Handlers\ImageUploadHandler;
use App\Models\Traits\ActiveUserHelper;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;


class User extends Authenticatable implements MustVerifyEmailContract
{
    use HasApiTokens, HasFactory, Notifiable, MustVerifyEmailTrait, HasRoles;
    use ActiveUserHelper;
    use Traits\LastActivedAtHelper;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'introduction',
        'avatar'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    public function topics()
    {
        return $this->hasMany(Topic::class);
    }
    public function isAuthorOf($model)
    {
        return $this->id == $model->user_id;
    }
    public function replies()
    {
        return $this->hasMany(Reply::class);
    }

    public function markAsRead()
    {
        $this->notification_count = 0;
        $this->save();
        $this->unreadNotifications->markAsRead();
    }
    public function setPasswordAttribute($value)
    {
        // 如果值的长度等于 60，即认为是已经做过加密的情况
        if (strlen($value) != 60) {

            // 不等于 60，做密码加密处理
            $value = bcrypt($value);
        }

        $this->attributes['password'] = $value;
    }

    public function setAvatarAttribute($fileName)
    {
        if (!app()->runningInConsole()) {
            $path = public_path() . "/uploads/images/avatars/$fileName";
            (Image::make($path))->resize(416, 416)->save();
            $folder_name = "uploads/images/avatars/" . date("Ym/d", time());
            $image = file_get_contents($path);
            app('upyun')->write("$folder_name/$fileName", $image);
            unlink($path);
            $this->attributes['avatar'] = config('services.upyun.domain') . "$folder_name/$fileName";
        }
    }

    public function replyNotify($instance)
    {
        // 如果要通知的人是当前用户，就不必通知了！
        if ($this->id == Auth::id()) {
            return;
        }
        // if (method_exists($instance, 'toDatabase')) {
        //     $this->increment('notification_count');
        // }
        $this->increment('notification_count');
        $this->notify($instance);
    }
}

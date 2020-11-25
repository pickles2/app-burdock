<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Auth\MustVerifyEmail;
use App\Notifications\CustomPasswordReset;
use App\Notifications\CustomVerifyEmail;
use Ramsey\Uuid\Uuid;

class User extends Authenticatable implements MustVerifyEmailContract
{
    use MustVerifyEmail, Notifiable;
	use SoftDeletes;

    /** プライマリーキーの型 */
    protected $keyType = 'string';

    /** プライマリーキーは自動連番か？ */
    public $incrementing = false;

	/**
	 * 日付へキャストする属性
	 *
	 * @var array
	 */
	protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'spaceid', 'organization', 'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Constructor
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // newした時に自動的にuuidを設定する。
        // DBにすでに存在するレコードをロードする場合は、あとから上書きされる。
        $this->attributes['id'] = Uuid::uuid4()->toString();
    }

    /**
     * リレーション (1対多の関係)
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function projects() // 複数形
    {
        // 記事を新しい順で取得する
        return $this->hasMany('App\Project')->latest();
    }

    /**
     * パスワードリセット通知の送信
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomPasswordReset($token));
    }

    /**
     * メール確認通知の送信
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new CustomVerifyEmail());
    }

    /**
     * 現在のユーザー、または引数で渡されたIDが管理者かどうかを返す
     *
     * @param  number  $id  User ID
     * @return boolean
     */
    public function isAdmin($id = null) {
        $id = ($id) ? $id : $this->id;
        return $id == config('admin_id');
    }
}

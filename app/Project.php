<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class Project extends Model
{

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
     * Constructor
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // newした時に自動的にuuidを設定する。
        // DBにすでに存在するレコードをロードする場合は、あとから上書きされる。
        $this->attributes['id'] = Uuid::uuid4()->toString();

        // 2021-03-20 追加された新しいカラム
        $this->attributes['git_main_branch_name'] = 'master';
    }

    /**
    * モデルのルートキーの取得
    *
    * @return string
    */
    public function getRouteKeyName()
    {
        return 'project_code';
    }

    /**
     * リレーション (従属の関係)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user() // 単数形
    {
        return $this->belongsTo('App\User');
    }
}

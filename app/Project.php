<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Project extends Model
{

    /** プライマリーキーの型 */
    protected $keyType = 'string';

    /** プライマリーキーは自動連番か？ */
    public $incrementing = false;

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
    * モデルのルートキーの取得
    *
    * @return string
    */
    public function getRouteKeyName()
    {
        return 'project_name';
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

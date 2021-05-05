<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class EventLog extends Model
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
    }

}

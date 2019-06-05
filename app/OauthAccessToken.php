<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OauthAccessToken extends Model
{

    /** プライマリーキーの型 */
    protected $keyType = 'string';

	/** 主キー */
	public $primaryKey = 'user_id';

	/** プライマリーキーは自動連番か？ */
	public $incrementing = false;

}

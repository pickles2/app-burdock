<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OauthAccesskey extends Model
{
	/** プライマリーキーの型 */
	protected $keyType = 'string';

	/** プライマリーキーは自動連番か？ */
	public $incrementing = false;
}

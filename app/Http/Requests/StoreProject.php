<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Project;
use Illuminate\Validation\Rule;

class StoreProject extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // 認可は別の箇所で行うので、ここでは素通りさせる
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(Project $project)
    {
		// ob_start();var_dump($project);error_log(ob_get_clean(),3,__DIR__.'/__dump.txt');
        return [
            'project_code' => ['required',Rule::unique('projects')->ignore($project->project_code)],
            'git_url' => 'max:400',
            'git_username' => '',
            'git_password' => '',
        ];
    }
}

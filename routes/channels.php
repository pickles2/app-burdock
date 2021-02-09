<?php

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.User.{id}', function ($user, $id) {
	return $user->id === $id;
});
Broadcast::channel('{project_code}---{branch_name}___publish.{user_id}', function ($user, $project_code, $branch_name, $user_id) {
	return $user->id === $user_id;
});
Broadcast::channel('{project_code}---{branch_name}___cce---{cce_id}.{user_id}', function ($user, $project_code, $branch_name, $cce_id, $user_id = null) {
	return $user->id === $user_id;
});

<?php

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

Route::get('/login', function () {
    return view('login');
});

Route::get('/', function () {
    return view('home');
});

Route::get('/teams', function () {
    return view('teams.index');
});

Route::get('/teams/create', function () {
    return view('teams.create');
});

Route::get('/teams/update/{id}', function ($id) {
    return view('teams.update', ['teamId' => $id]);
});

Route::get('/teams/{id}', function () {
    return view('teams.show');
});

Route::get('/project/create/{team_id}', function ($team_id) {
    return view('projects.create', ['team_id' => $team_id]);
});

Route::get('project/{id}', function ($id) {
    return view('projects.show', ['id' => $id]);
});

Route::get('project/update/{project_id}', function ($project_id) {
    return view('projects.update', ['project_id' => $project_id]);
});

Route::get('task/create/{project_id}', function ($project_id) {
    return view('tasks.create', ['project_id' => $project_id]);
});

Route::get('task/update/{project_id}/{task_id}', function ($project_id, $task_id) {
    return view('tasks.update', ['project_id' => $project_id, 'task_id' => $task_id]);
});

Route::get('/test', function () {
    return view('test');
});

Route::get('/teamInvite/{id}', function ($id) {
    return view('teams.invite', ['teamId' => $id]);
});

Route::post('/notification', function () {
    $data = Request::all();
    return view('notification', ['data' => $data]);
});

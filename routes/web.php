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

Route::get('task/create/{project_id}', function ($project_id) {
    return view('tasks.create', ['project_id' => $project_id]);
});

Route::get('/test', function () {
    return view('test');
});

Route::post('/notification', function () {
    $data = Request::all(); // 注意這裡是 facade 的方法
    return view('notification', ['data' => $data]);
});

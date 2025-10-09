<?php

namespace App\Http\Controllers;

use App\Events\TestCreate;

class TestController extends Controller
{

    public function test()
    {
        event(new TestCreate());

        return 'test';
    }
}

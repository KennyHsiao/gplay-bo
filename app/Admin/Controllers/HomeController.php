<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use Xn\Admin\Controllers\Dashboard;
use Xn\Admin\Facades\Admin;
use Xn\Admin\Layout\Column;
use Xn\Admin\Layout\Content;
use Xn\Admin\Layout\Row;
use Xn\Admin\Widgets\Box;

class HomeController extends Controller
{
    public function index(Content $content)
    {
        return $content
        ->title('Dashboard')
        ->description('Description...')
        ->body(view('dashboard'));
    }
}

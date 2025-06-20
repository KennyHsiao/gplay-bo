<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

trait ModelRoute
{
    /**
     * 註冊預設的Route
     *
     * @param [type] $router
     * @param array $middleware
     * @return void
     */
    static function routes($router, $middleware = []) {
        $router->group([
            'prefix'        => 'dx',
            'namespace'     => '\App\Models',
            'middleware'    => $middleware
        ], function($router){
            $_ = explode("\\", self::class);
            $name = strtolower(Str::plural(end($_)));
            $router->match(['get', 'head'], $name, [self::class, "dxIndex"])->name("dx.{$name}.index");
            $router->match(['post'], $name, [self::class, "dxStore"])->name("dx.{$name}.store");
            $router->match(['put', 'patch'], "$name/{{$name}}", [self::class, "dxUpdate"])->name("dx.{$name}.update");
            $router->match(['delete'], "$name/{{$name}}", [self::class, "dxDestroy"])->name("dx.{$name}.destroy");
        });
    }

    public function dxIndex(Request $request) {
        return response()->json($this->all());
    }

    public function dxStore(Request $request) {

    }

    public function dxUpdate(Request $request, $id) {
        $data = $this->where($this->getKeyName(), $id);
    }

    public function dxDestroy(Request $request, $id) {
        $data = $this->where($this->getKeyName(), $id);
    }
}

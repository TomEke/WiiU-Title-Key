<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('index');
});

Route::get("/json", function() {
    return \App\Title::all()->toJson();
});

Route::post('/', function(Illuminate\Http\Request $request) {

    $titleID = strtolower($request->input('titleID'));
    $titleKey = strtolower($request->input('titleKey'));

    if (\App\Title::find($titleID)) {
        return "success";
    }

    $title = new \App\Title;
    $title->titleID = $titleID;
    $title->titleKey = $titleKey;

    if ($title->checkValid()) {
        $title->parseIcon();
        $title->save();
        return "success";
    }
    return "failure";

});
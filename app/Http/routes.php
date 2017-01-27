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

Route::post('/uploadticket', function(Illuminate\Http\Request $request) {
    $key = Storage::get("key/XS0000000c.pem");

    //Make sure the filesize isn't too big
    //100000 bytes is about 100 kilobytes, the biggest ticket is about 2kb
    if (filesize($request->file('file')->getRealPath()) > 100000) {
        return "File too big";
    }

    //Save the file
    $name = uniqid() . $request->file('file')->getClientOriginalName();
    $file = file_get_contents($request->file('file')->getRealPath());
    Storage::put($name, $file);

    //TODO proper offsets?
    $sig = substr($file, 0x4, 0x100);
    $data = substr($file, 0x140, 0x210);

    //Check if the ticket has been modified
    //TODO do the modification online
    if (bin2hex(substr($file, 0x1, 0x1)) != 1) {
        return "Please modify the ticket before uploading";
    }

    //Validates the signature
    if (openssl_verify($data, $sig, $key, "RSA-SHA256")) {

        $titleID = strtolower(bin2hex(substr($data, 0X9C, 0X8)));
        $titleKey = strtolower(bin2hex(substr($data, 0X7F, 0X10)));

        //Check the console ID
        if (bin2hex(substr($data, 0x98, 0x4)) != 0) {
            //Check the title key anyways
            $title = \App\Title::firstOrCreate(["titleID" => $titleID]);
            //Don't check if there is already a title key
            if ($title->titleKey == null) {
                $title->titleKey = $titleKey;
                if ($title->checkValid()) {
                    $title->save();
                }
            }
            return "Is this a disk ticket?";
        }

        $test = \App\Title::find($titleID);
        if ($test == null) {
            Storage::put("tickets/" . $titleID . ".tik", $file);
            //Get a name so we have something with this ticket
            $title = \App\Title::firstOrCreate(["titleID" => $titleID]);
            $title->ticket = true;
            $title->save();

            if ($title->titleKey == null) {
                //Save the titlekey if it works
                $title->titleKey = $titleKey;
                if ($title->checkValid()) {
                    $title->save();
                }
            }
        } else {
            if ($test->ticket == false) {
                Storage::put("tickets/" . $titleID . ".tik", $file);
            }
            $test->ticket = true;
            $test->save();
        }

       return "Added, Thanks";
    } else {
        return "Invalid Ticket";
    }
});

Route::post('/uploadkeystxt', function(Illuminate\Http\Request $request) {
    //Make sure the filesize isn't too big
    //100000 bytes is about 100 kilobytes, the biggest ticket is about 2kb
    if (filesize($request->file('file')->getRealPath()) > 100000) {
        return "File too big";
    }

    //Save the file
    $name = uniqid() . $request->file('file')->getClientOriginalName();
    $file = file_get_contents($request->file('file')->getRealPath());
    Storage::put($name, $file);


    $lines = explode("\n", $file);
    foreach ($lines as $line) {
        $line_split = explode(" ", $line);
        if (count($line_split) > 1) {
            $titleID = strtolower($line_split[0]);
            $titleKey = strtolower($line_split[1]);

            if (strlen($titleID) != 16 && strlen($titleKey != 32)) {
                continue;
            }

            $test = \App\Title::find($titleID);
            if ($test && $test->titleKey != null) {
                continue;
            }

            if ($test != null) {
                $title = $test;
            } else {
                $title = new \App\Title;
                $title->titleID = $titleID;
            }
            $title->titleKey = $titleKey;

            if ($title->checkValid()) {
                $title->save();
                continue;
            }
        }
    }

    return "Thanks";
});

Route::get("/ticket/{id}.tik", function($id) {
   $title = \App\Title::find($id);
    if ($title != null && $title->ticket != false) {
        return Response::make( Storage::get("tickets/" . $title->titleID . ".tik"))->header("Content-Disposition", "Attachment; filename=title.tik")->header("Content-Type", "application/octet-stream");
    }
});

Route::get('/', function () {
    return view('index');
});

Route::get("/json", function() {
    return \App\Title::all()->toJson();
});

Route::post('/', function(Illuminate\Http\Request $request) {

    $titleID = strtolower($request->input('titleID'));
    $titleKey = strtolower($request->input('titleKey'));

    $test = \App\Title::find($titleID);
    if ($test && $test->titleKey != null) {
        return "success";
    }

    if ($test != null) {
        $title = $test;
    } else {
        $title = new \App\Title;
        $title->titleID = $titleID;
    }
    $title->titleKey = $titleKey;

    if ($title->checkValid()) {
        $title->save();
        return "success";
    }
    return "failure";

});


Route::get('/rss', function() {
    $feed = App::make("feed");

    $feed->setCache(60);

    if (!$feed->isCached()) {
        $titles = \App\Title::orderBy('created_at', 'desc')->take(40)->get();

        $feed->title = 'Wii U Title Keys';
        $feed->description = 'Newest Keys';
        $feed->setDateFormat('datetime'); // 'datetime', 'timestamp' or 'carbon'
        $feed->pubdate = $titles[0]->created_at;
        $feed->lang = 'en';
        $feed->setShortening(true); // true or false
        $feed->setTextLimit(100); // maximum length of description text

        foreach ($titles as $title) {
            $name = $title->name;
            if ($name == null) {
                $name = $title->titleID;
            }
            $feed->add($name, null, URL::to("/#" . $title->titleID), $title->created_at, $title->titleID, $title->titleKey);
        }
    }
    return $feed->render('rss');
});

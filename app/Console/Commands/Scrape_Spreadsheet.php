<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Scrape_Spreadsheet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:spreadsheet';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $csv = file_get_contents("https://docs.google.com/feeds/download/spreadsheets/Export?key=1l427nnapxKEUBA-aAtiwAq1Kw6lgRV-hqdocpKY6vQ0&exportFormat=csv&gid=923297102");
        $lines = explode("\r\n", $csv);
        unset($lines[0]);//First header row
        foreach ($lines as $line) {
            $split = str_getcsv($line);
            $titleID = strtolower($split[9]);
            $titleKey = $split[6];
            echo $titleID . "\t" . $titleKey . "\n";

            if (\App\Title::find($titleID)) {
                continue;
            }

            $title = new \App\Title;
            $title->titleID = $titleID;
            $title->titleKey = $titleKey;

            if ($title->checkValid()) {
                $title->parseIcon();
                $title->save();
                echo "ADDDED!";
            }


        }
    }
}

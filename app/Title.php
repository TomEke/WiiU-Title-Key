<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Title extends Model
{
    protected $primaryKey = "titleID";

    protected $hidden = ['created_at', 'updated_at'];

    protected $guarded = ['titleKey'];

    public $incrementing = false;

    public function getDecTitleKeyAttribute() {
        return bin2hex(openssl_decrypt(base64_encode(hex2bin($this->titleKey)), 'AES-128-CBC', hex2bin(env('COMMON_KEY')), OPENSSL_ZERO_PADDING, hex2bin($this->titleID . "0000000000000000")));
    }

    public function getTypeAttribute() {
        $header = strtoupper(substr($this->titleID, 0, 8));
        if ($header == "00050010" || $header == "0005001B") {
            return "System Application";
        } else if ($header == "00050000") {
            return "eShop/Application";
        } else if ($header == "00050002") {
            return "Demo";
        } else if ($header == "0005000E") {
            return "Patch";
        } else if ($header == "0005000C") {
            return "DLC";
        }
        return "Unknown";
    }

    public function checkValid() {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, "http://nus.cdn.c.shop.nintendowifi.net/ccs/download/" . $this->titleID . "/tmd");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $tmd = curl_exec($curl);

        if (curl_getinfo($curl)['http_code'] != 200) {
            return false;
        }

        $contentCount  = unpack('n', substr($tmd, 0x206, 0x2))[1];
        //TODO check to make sure it's found and if the title IDs match

        for ($i = 0; $i < $contentCount; $i++) {

            $cOff = 0xB04+(0x30*$i);
            //Unpack the thing at that offset as a 16-bit big-endain, convert it to hex, and then leftpad it with zeroes
            $cIdx = str_pad(dechex(unpack('N', substr($tmd, $cOff, 4))[1]), 8, '0', STR_PAD_LEFT);

            curl_setopt($curl, CURLOPT_URL, "http://nus.cdn.c.shop.nintendowifi.net/ccs/download/" . $this->titleID ."/" . $cIdx);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

            $output = curl_exec($curl);

            if (curl_getinfo($curl)['http_code'] != 200) {  //206 Partial content
                //Wrong container
                continue;
            }

            $test = openssl_decrypt(base64_encode($output), 'AES-128-CBC', hex2bin($this->decTitleKey), OPENSSL_ZERO_PADDING);

            if ($test == false) {
                return false;
            }

            if (substr($test, 0, 0x3) == "FST") {
                return true;
            }
        }

    }

    public function parseIcon() {
        $keys = [
            '4ab9a40e146975a84bb1b4f3ecefc47b',
            '90a0bb1e0e864ae87d13a6a03d28c9b8',
            'ffbb57c14e98ec6975b384fcf40786b5',
            '80923799b41f36a6a75fb8b48c95f66f'
        ];
        $iv = "a46987ae47d82bb4fa8abc0450285fa4";

        $regions = [
            0 => "JPN",
            1 => "USA",
            2 => "EUR",
            3 => "EUR",
            4 => "CHN",
            5 => "KOR",
            6 => "TWN",
            7 => "???",
            8 => "???",
        ];

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => 'https://idbe-wup.cdn.nintendo.net/icondata/10/' . strtoupper($this->titleID) .'.idbe'
        ]);

        $output = curl_exec($curl);
        if (curl_getinfo($curl)['http_code'] == 403) {
            return;
        }
        $keyslot = intval(bin2hex(substr($output, 1, 1)));

        $data = substr($output, 2);

        $test = openssl_decrypt(base64_encode($data), 'AES-128-CBC', hex2bin($keys[$keyslot]), OPENSSL_ZERO_PADDING, hex2bin($iv));

        $name = trim(mb_convert_encoding(substr($test, 208+512, 256), 'UTF-8', 'UTF-16BE'));
        $publisher = trim(mb_convert_encoding(substr($test, 464+512, 256), 'UTF-8', 'UTF-16BE'));   //TODO
        $regionCode = unpack('N', substr($test, 48, 4))[1];

        if ($regionCode == 4294967295) {
            $region = "ALL";
        } else {
            for ($i = 0; $i < 8; $i++) {
                if (($regionCode & (1 << $i)) != 0) {
                    $region = $regions[$i];
                    break;
                }
            }
        }

        $this->name = $name;
        $this->region = $region;

    }

}

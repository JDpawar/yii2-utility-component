<?php

namespace app\components;

use Yii;
use yii\base\Component;

class Utility extends Component
{
    public $googleApiServerKey;

    public function getUniqueId($length = 16)
    {
        $uniqueID = str_replace('.', '', microtime(true));
        if (strlen($uniqueID) < $length) {
            $uniqueID = $uniqueID . substr(rand(1111111, 9999999), 0, $length - strlen($uniqueID));
        }
        return $uniqueID;
    }

    public function isEmailValid($email = null)
    {
        if ($email != null && preg_match('/^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-z]{2,4})$/', $email)) {

            return true;
        }

        return false;
    }

    public function isMobileValid($mobile = null)
    {
        if ($mobile != null && preg_match('/^[a-zA-Z0-9 ]+$/', $mobile)) {

            return true;
        }
        return false;
    }

    public function haversineGreatCircleDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371)
    {
        $latFrom 	= deg2rad($latitudeFrom);
        $lonFrom 	= deg2rad($longitudeFrom);
        $latTo 		= deg2rad($latitudeTo);
        $lonTo 		= deg2rad($longitudeTo);
        $latDelta 	= $latTo - $latFrom;
        $lonDelta	= $lonTo - $lonFrom;
        $angle 		= 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return $angle * $earthRadius;
    }

    public function getLatLng($address)
    {
        $lat = 0;
        $lng = 0;
        $params = array(
            'sensor'    => 'false',
            'address'   => $address,
            'key'       => $this->googleApiServerKey,
        );
        $url = 'https://maps.google.com/maps/api/geocode/json' . '?' . http_build_query($params);
        $output = Yii::$app->curl->get($url);
        $output = json_decode($output);
        if(isset($output->results[0]->geometry->location->lat)) {
            $lat = $output->results[0]->geometry->location->lat;
            $lng = $output->results[0]->geometry->location->lng;
        }
        return array('lat' => $lat, 'lng' => $lng);
    }

    public static function downloadFile($url, $saveTo)
    {
        $ch = curl_init ($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        $raw = curl_exec($ch);
        curl_close ($ch);
        if (file_exists($saveTo)) {
            unlink($saveTo);
        }
        $fp = fopen($saveTo, 'x');
        fwrite($fp, $raw);
        fclose($fp);
    }

    public function convert($number, $fromBase=10, $toBase=62)
    {
        if($toBase > 62 || $toBase < 2) {
            trigger_error("Invalid base (".$toBase."). Max base can be 62. Min base can be 2.", E_USER_ERROR);
        }
        //OPTIMIZATION: no need to convert 0
        if("{$number}" === '0') {
            return 0;
        }

        //OPTIMIZATION: if to and from base are same.
        if($fromBase == $toBase){
            return $number;
        }

        //OPTIMIZATION: if base is lower than 36, use PHP internal function
        if($fromBase <= 36 && $toBase <= 36) {
            // for lower base, use the default PHP function for faster results
            return base_convert($number, $fromBase, $toBase);
        }

        // char list starts from 0-9 and then small alphabets and then capital alphabets
        // to make it compatible with existing base_convert function
        $charList = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        if($fromBase < $toBase) {
            // if conversion is from lower base to higher base
            // first get the number into decimal and then convert it to higher base from decimal;

            //get the list of valid characters
            $charList = substr($charList, 0, $toBase);

            if($number == 0) {
                return 0;
            }
            $converted = '';
            while($number > 0) {
                $converted = $charList{bcmod($number, $toBase)} . $converted;
                $number = bcdiv($number, $toBase);
            }
            return $converted;
        } else {
            // if conversion is from higher base to lower base;
            // first convert it into decimal and the convert it to lower base with help of same function.
            $number = "{$number}";
            $length = strlen($number);
            $decimal = 0;
            $i = 0;
            while($length > 0) {
                $char = $number{$length-1};
                $pos = strpos($charList, $char);
                if($pos === false){
                    trigger_error("Invalid character in the input number: ".($char), E_USER_ERROR);
                }
                $decimal = bcadd($decimal, bcmul($pos, bcpow($fromBase, $i)));
                $length --;
                $i++;
            }
            return self::convert($decimal, 10, $toBase);
        }
    }

    public function init() {    }
} 

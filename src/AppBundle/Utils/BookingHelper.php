<?php
/**
 * Created by PhpStorm.
 * User: chriswest
 * Date: 02/02/2018
 * Time: 14:29
 */

namespace AppBundle\Utils;


use Symfony\Component\HttpFoundation\Session\Session;

class BookingHelper
{
    /**
     * @var Session $sessionBag
     */
    private $session;
    private $dayRate,
            $nightRate,
            $morningRate,
            $afternoonRate,
            $eveningRate,
            $googleMapsKey;

    public function __construct($dayRate, $nightRate, $morningRate, $afternoonRate, $eveningRate, $googleMapsKey, Session $session)
    {
        $this->session = $session;
        $this->dayRate = $dayRate;
        $this->nightRate = $nightRate;
        $this->morningRate = $morningRate;
        $this->afternoonRate = $afternoonRate;
        $this->eveningRate = $eveningRate;
        $this->googleMapsKey = $googleMapsKey;
    }

    public function resetBooking()
    {
        $this->session->clear();
    }

    public function startBooking()
    {
        $this->session->set('booking_id', uniqid());
    }

    public function getDistance($from, $to)
    {
        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?units=imperial&origins=$to&destinations=$from&key=" . $this->googleMapsKey;

        // Initiate curl
        $ch = curl_init();
        // Disable SSL verification
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // Will return the response, if false it print the response
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Set the url
        curl_setopt($ch, CURLOPT_URL,$url);
        // Execute
        $result=curl_exec($ch);
        // Closing
        curl_close($ch);

        return json_decode($result, true);
    }

    public function getPrice($distance, $pickupTime)
    {
        $rate = $this->dayRate;
        if($pickupTime >= "000000" && $pickupTime <= "060000") {
            $rate = $this->nightRate;
        } elseif($pickupTime >= "060001" && $pickupTime <= "090000") {
            $rate = $this->morningRate;
        } elseif($pickupTime >= "090001" && $pickupTime <= "130000") {
            $rate = $this->dayRate;
        } elseif($pickupTime >= "130001" && $pickupTime <= "170000") {
            $rate = $this->afternoonRate;
        } elseif($pickupTime >= "170001" && $pickupTime <= "210000") {
            $rate = $this->eveningRate;
        } elseif($pickupTime >= "210001" && $pickupTime <= "235959") {
            $rate = $this->nightRate;
        }

        $price = preg_replace("/[^0-9,.]/", "", number_format((float)$distance * $rate, 2, '.', ''));

        return $price;
    }
}
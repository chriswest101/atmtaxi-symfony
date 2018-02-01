<?php
/**
 * Created by PhpStorm.
 * User: chriswest
 * Date: 19/01/2018
 * Time: 10:52
 */

namespace AppBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class BookController extends Controller
{
    /**
     * @Route("/book/", name="book_step_one", options={"expose"=true})
     * @Method("GET")
     */
    public function stepOneAction(Request $request)
    {
        $this->resetBooking();
        $this->startBooking();

        return $this->render(
            "book/step_one.html.twig",
            array(
                'page' => "subpage",
                "id" => $this->get('session')->get('booking_id')
            )
        );
    }

    /**
     * @Route("/book/", name="book_step_two", options={"expose"=true})
     * @Method({"POST", "GET"})
     */
    public function stepTwoAction(Request $request)
    {
        if($request->getMethod() == "POST") {
            if ($request->request->get('id') != $this->get('session')->get('booking_id') || $request->request->get('from') == "" || $request->request->get('latlong') == "") {
                $this->addFlash(
                    'warning',
                    'Please provide your pickup location.'
                );
                return $this->redirectToRoute('book_step_one');
            }

            $this->get('session')->set('booking_from', $request->request->get('from'));
            $this->get('session')->set('booking_from_latlong', $request->request->get('latlong'));
        } else {
            if (!$request->get('check') || !isset($request->get('check'))) {
                return $this->redirectToRoute('book_step_one');
            }
        }

        return $this->render(
            "book/step_two.html.twig",
            array(
                'page' => "subpage",
                "id" => $this->get('session')->get('booking_id')
            )
        );
    }

    /**
     * @Route("/book/", name="book_step_three", options={"expose"=true})
     * @Method({"POST", "GET"})
     */
    public function stepThreeAction(Request $request)
    {
        if($request->getMethod() == "POST") {
            if ($request->request->get('id') != $this->get('session')->get('booking_id') || !$request->request->get('to') || !$request->request->get('latlong')) {
                $this->addFlash(
                    'warning',
                    'Please provide your dropoff location.'
                );
                return $this->redirectToRoute('book_step_two', ['check' => 1]);
            }
            $this->get('session')->set('booking_to', $request->request->get('to'));
            $this->get('session')->set('booking_to_latlong', $request->request->get('latlong'));
        } else {
            if (!$request->get('check') || !isset($request->get('check'))) {
                return $this->redirectToRoute('book_step_one');
            }
        }

        return $this->render(
            "book/step_three.html.twig",
            array(
                'page' => "subpage",
                'id' => $this->get('session')->get('booking_id'),
                'to' => $this->get('session')->get('booking_to'),
                'from' => $this->get('session')->get('booking_from'),
            )
        );
    }

    /**
     * @Route("/book/", name="book_step_four", options={"expose"=true})
     * @Method({"POST", "GET"})
     */
    public function stepFourAction(Request $request)
    {
        if($request->getMethod() == "POST") {
            if (
                $request->request->get('id') != $this->get('session')->get('booking_id') ||
                !$request->request->get('date') ||
                !$request->request->get('time') ||
                !$request->request->get('phone') ||
                !$request->request->get('name') ||
                !$request->request->get('email') ||
                !$request->request->get('no_of_people')
            ) {
                $this->addFlash(
                    'warning',
                    'Please fill in all the fields.'
                );
                return $this->redirectToRoute('book_step_three', ['check' => 1]);
            }

            $this->get('session')->set('booking_pickup_time', $request->request->get('date')." ".$request->request->get('time'));
            $this->get('session')->set('booking_contact_number', $request->request->get('phone'));
            $this->get('session')->set('booking_no_of_people', $request->request->get('no_of_people'));
            $this->get('session')->set('booking_name', $request->request->get('name'));
            $this->get('session')->set('booking_email', $request->request->get('email'));

            $journey = $this->getDistance($this->get('session')->get('booking_from_latlong'), $this->get('session')->get('booking_to_latlong'));
            $this->get('session')->set('booking_distance', (isset($journey['rows'][0]['elements'][0]['distance']['text']) ? $journey['rows'][0]['elements'][0]['distance']['text'] : "N/A"));
            $this->get('session')->set('booking_price', $this->getPrice($this->get('session')->get('booking_distance'), $this->get('session')->get('booking_pickup_time')));
        } else {
            if (!$request->get('check') || !isset($request->get('check'))) {
                return $this->redirectToRoute('book_step_one');
            }
        }

        return $this->render(
            "book/step_four.html.twig",
            array(
                'page' => "subpage",
                'id' => $this->get('session')->get('booking_id'),
                'to' => $this->get('session')->get('booking_to'),
                'from' => $this->get('session')->get('booking_from'),
                'price' => $this->get('session')->get('booking_price'),
                'distance' => $this->get('session')->get('booking_distance'),
                'pickup_time' => $this->get('session')->get('booking_pickup_time'),
                'contact_number' => $this->get('session')->get('booking_contact_number'),
                'no_of_people' => $this->get('session')->get('booking_no_of_people'),
                'name' => $this->get('session')->get('booking_name'),
                'email' => $this->get('session')->get('booking_email'),
            )
        );
    }

    /**
     * @Route("/book/", name="book_step_five", options={"expose"=true})
     * @Method({"POST", "GET"})
     */
    public function stepFiveAction(Request $request)
    {
        if ($request->request->get('id') != $this->get('session')->get('booking_id')) {
            $this->addFlash(
                'warning',
                'Something went wrong! Please try again.'
            );
            return $this->redirectToRoute('book_step_one');
        }

        //Recipients
        //$to = 'hello@atmtaxi.co.uk';
        $to = 'chriswest1010@gmail.com';

        //Send Email
        $mailResponse = $this->get('appBundle.sendMail')->send('Booking Confirmation', $to, $this->get('session')->all(), 'booking', $this->get('session')->get('booking_email'));

        if(!$mailResponse) {
            $this->addFlash(
                'warning',
                'Something went wrong! Please try again.'
            );
            return $this->redirectToRoute('book_step_one');
        }

        return $this->render(
            "book/step_five.html.twig",
            array(
                'page' => "subpage",
                'id' => $this->get('session')->get('booking_id'),
                'name' => $this->get('session')->get('booking_name'),
            )
        );
    }

    private function resetBooking()
    {
        $this->get('session')->clear();
    }

    private function startBooking()
    {
        $this->get('session')->set('booking_id', uniqid());
    }

    private function getDistance($from, $to)
    {
        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?units=imperial&origins=$to&destinations=$from&key=" . $this->getParameter('google_maps_matrix_key');

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

    private function getPrice($distance, $pickupTime)
    {
        $rate = $this->getParameter('day_rate');
        if($pickupTime >= "000000" && $pickupTime <= "060000") {
            $rate = $this->getParameter('night_rate');
        } elseif($pickupTime >= "060001" && $pickupTime <= "090000") {
            $rate = $this->getParameter('morning_rate');
        } elseif($pickupTime >= "090001" && $pickupTime <= "130000") {
            $rate = $this->getParameter('day_rate');
        } elseif($pickupTime >= "130001" && $pickupTime <= "170000") {
            $rate = $this->getParameter('afternoon_rate');
        } elseif($pickupTime >= "170001" && $pickupTime <= "210000") {
            $rate = $this->getParameter('evening_rate');
        } elseif($pickupTime >= "210001" && $pickupTime <= "235959") {
            $rate = $this->getParameter('night_rate');
        }

        $price = preg_replace("/[^0-9,.]/", "", number_format((float)$distance * $rate, 2, '.', ''));

        return $price;
    }
}
<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('homepage/index.html.twig', ['page' => 'homepage']);
    }

    /**
     * @Route("/contact/", name="contact", options={"expose"=true})
     * @Method("POST")
     */
    public function contactAction(Request $request)
    {
        $response = false;
        $postData = $request->request->all();

        if ( empty( $postData['name'] ) ) {
            $response['name'] = true;
        }
        if ( !empty( $postData['phone'] ) ) {
            $response['check'] = true;
        }
        if ( empty( $postData['email'] ) ) {
            $response['email'] = true;
        }
        if ( empty( $postData['message'] ) ) {
            $response['message'] = true;
        }

        if ( is_array($response) ) {
            $response['sent'] = false;
            return $this->json($response);
        }

        //Recipients
        //$to = 'hello@atmtaxi.co.uk';
        $to = 'chriswest1010@gmail.com';

        //Subject
        $subject = 'Contact Form';

        //Send Email
        $mailResponse = $this->get('appBundle.sendMail')->send($subject, $to, $postData, 'contact_form', $postData['email']);

        if($mailResponse) {
            $response['sent'] = true;
            return $this->json($response);
        } else {
            $response['sent'] = false;
            $response['error1'] = 'Message could not be sent.';
            return $this->json($response);
        }
    }
}

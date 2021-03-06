<?php 

namespace App\Service;

use DateTime;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

class DateFormatService{

    // Format la date en JSON
    public function formatDate($date) {
        
        $date = new DateTime($date);
        
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizer = array(new DateTimeNormalizer(), new ObjectNormalizer());
        $serializer = new Serializer($normalizer, $encoders);
        $date = $serializer->serialize($date, 'json');

        // Retire les quote en trop.
        $date = str_replace('"', "", $date);

        return $date;   
    }
}
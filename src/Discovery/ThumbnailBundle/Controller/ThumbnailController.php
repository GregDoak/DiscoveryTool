<?php

namespace Discovery\ThumbnailBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ThumbnailController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction(Request $request)
    {
        $file = $request->get('file');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, str_replace('__X__', '&', $file));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // good edit, thanks!
        curl_setopt(
          $ch,
          CURLOPT_BINARYTRANSFER,
          1
        ); // also, this seems wise considering output is image.
        $data = curl_exec($ch) or die("Error: ".curl_error($ch));
        curl_close($ch);

        header('Content-type: image/jpeg');
        $test = \imagecreatefromstring($data);


        imagejpeg($test, null, 100);

        return new Response();
    }
}

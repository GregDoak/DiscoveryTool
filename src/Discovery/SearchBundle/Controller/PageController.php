<?php

namespace Discovery\SearchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class PageController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction(Request $request)
    {
        $response = [];

        $response['query'] = $request->get('q');

        if (!empty($request->get('q'))) {
            $response['string'] = "Search results for ".$request->get('q');
        }

        return $this->render(
          'DiscoverySearchBundle:Default:index.html.twig',
          $response
        );
    }
}

<?php

namespace Discovery\ErrorBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class ErrorController extends Controller
{
    /**
     * @Route("/admin/errors/{baseTable}/{baseTableId}")
     * @Template("@DiscoveryError/Default/index.html.twig")
     */
    public function errorsIndexAction($baseTable, $baseTableId)
    {
        $data = [];

        $errors = $this->getDoctrine()
          ->getRepository('DiscoveryErrorBundle:Error')
          ->findBy(
            [
              'baseTable' => $baseTable,
              'baseTableID' => $baseTableId,
            ],
            [
              'createdOn' => 'DESC',
            ]
          );

        foreach ($errors as $error) {
            $data[] = [
              'id' => $error->getId(),
              'createdOn' => $error->getCreatedOn(),
              'message' => $error->getMessage(),
            ];
        }

        return array(
          'errors' => $data,
        );
    }
}

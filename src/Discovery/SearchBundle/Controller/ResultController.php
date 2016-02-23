<?php

namespace Discovery\SearchBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

class ResultController extends Controller
{
    /**
     * @Route("/results.json")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        try {

            $q = (!(empty($request->get('q')))) ? $request->get('q') : '*';
            $start = (!(empty($request->get('start')))) ? $request->get(
              'start'
            ) : 0;
            $limit = (!(empty($request->get('limit')))) ? $request->get(
              'limit'
            ) : 0;

            $startConstraint = new Assert\Type('numeric');
            $startConstraint->message = "Start should be a number";
            $limitConstraint = new Assert\Type('numeric');
            $limitConstraint->message = "Limit should be a number";

            $validator = $this->get('validator');
            $errorList = [];
            $errorList[] = $validator->validate($start, $startConstraint);
            $errorList[] = $validator->validate($limit, $limitConstraint);

            $data = [];

            if (sizeof($errorList) !== 0) {
                foreach ($errorList as $error) {
                    if (isset($error[0])) {
                        throw new Exception($error[0]->getMessage(), 500);
                    }
                }
            }

            $host = $this->container->getParameter('solr_host');
            $port = $this->container->getParameter('solr_port');
            $instance = $this->container->getParameter('solr_instance');

            $url = "http://".$host.":".$port."/solr/".$instance."/select?";
            $url .= "&wt=json";
            $url .= "&hl=true&hl.fl=title&hl.simple.pre=%3Cem%3E&hl.simple.post=%3C%2Fem%3E";
            $url .= "&facet=true&facet.field=collections";
            $url .= "&q=title:*".urlencode($q)."*";
            $url .= "&start=".$start;
            $url .= "&rows=".$limit;

            $json = json_decode(file_get_contents($url));

            foreach ($json->response->docs as $doc) {
                $data[] = $doc;
            }

            $facetFields = sizeof(
              $json->facet_counts->facet_fields
            ) ? $json->facet_counts->facet_fields : [];
            $facetData = [];

            foreach ($facetFields as $field => $facets) {
                if (sizeof($facets) > 0) {
                    $x = 0;
                    do {
                        $facetData[ucfirst(
                          $field
                        )][$facets[$x]] = $facets[$x + 1];
                        $x = $x + 2;

                    } while ($x < (sizeof($facets) - 1));
                }
            }

            $results = [
              'status' => true,
              'code' => 200,
              'data' => $data,
              'facets' => $facetData,
              'count' => sizeof($data),
            ];

        } catch (\Exception $exception) {
            $results = [
              'status' => false,
              'code' => $exception->getCode(),
              'message' => $exception->getMessage(),
            ];
        }

        return new JsonResponse($results);
    }
}

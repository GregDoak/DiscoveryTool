<?php

namespace Discovery\eBookBundle\Controller;

use Discovery\eBookBundle\Entity\eBook;
use Discovery\eBookBundle\Form\eBookCreateType;
use Discovery\eBookBundle\Form\eBookDeleteType;
use Discovery\eBookBundle\Form\eBookUpdateType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class eBookController extends Controller
{
    /**
     * @Route("/admin/ebooks")
     * @Template("@DiscoveryeBook/Default/index.html.twig")
     */
    public function eBooksIndexAction()
    {
        $data = [];

        $ebooks = $this->getDoctrine()
          ->getRepository('DiscoveryeBookBundle:eBook')
          ->findAll();

        foreach ($ebooks as $ebook) {
            $errors = $this->getDoctrine()->getRepository(
              'DiscoveryErrorBundle:Error'
            )->findBy(
              [
                'baseTable' => 'EBOOKS',
                'baseTableID' => $ebook->getIsbn(),
              ]
            );

            $data[] = [
              'isbn' => $ebook->getIsbn(),
              'googleUid' => $ebook->getGoogleUID(),
              'createdOn' => $ebook->getCreatedOn(),
              'updatedOn' => $ebook->getUpdatedOn(),
              'processed' => $ebook->getProcessed(),
              'attemptCount' => $ebook->getAttemptCount(),
              'url' => $ebook->getUrl(),
              'linkType' => $ebook->getLinkType(),
              'errors' => sizeof($errors),
            ];
        }

        return array(
          'ebooks' => $data,
        );
    }

    /**
     * @Route("/admin/ebook/create")
     * @Template("@DiscoveryeBook/Default/create.html.twig")
     */
    public function eBookCreateAction(Request $request)
    {
        $ebook = new eBook();

        $form = $this->createForm(
          'Discovery\eBookBundle\Form\eBookCreateType',
          $ebook,
          [
            'action' => '#',
          ]
        );

        $form->handleRequest($request);

        if ($form->isValid()) {
            $ebook->setCreatedOnValue();
            $ebook->setProcessedValue();
            $ebook->setAttemptCountValue();
            $em = $this->getDoctrine()->getManager();
            $em->persist($ebook);
            $em->flush();

            return $this->redirect('/admin/ebooks');
        }

        return array(
          'ebook' => $ebook,
          'form' => $form->createView(),
        );

    }

    /**
     * @Route("/admin/ebook/update/{id}")
     * @Template("@DiscoveryeBook/Default/update.html.twig")
     */
    public function eBookUpdateAction($id)
    {
        $request = $this->container->get('request');

        $ebook = $this->getDoctrine()->getRepository(
          'DiscoveryeBookBundle:eBook'
        )->find($id);

        if ($ebook) {
            $form = $this->createForm(
              'Discovery\eBookBundle\Form\eBookUpdateType',
              $ebook,
              [
                'action' => '#',
              ]
            );

            $form->handleRequest($request);

            if ($form->isValid()) {
                $ebook->setProcessedValue();
                $ebook->setAttemptCountValue();
                $em = $this->getDoctrine()->getManager();
                $em->persist($ebook);
                $em->flush();
                $message = "eBook successfully updated.";
            }
        }

        return array(
          'ebook' => $ebook,
          'form' => $form->createView(),
          'message' => (isset($message)) ? $message : '',
        );
    }

    /**
     * @Route("/admin/ebook/delete/{id}")
     * @Template("@DiscoveryeBook/Default/delete.html.twig")
     */
    public function eBookDeleteAction($id)
    {
        $request = $this->container->get('request');

        $ebook = $this->getDoctrine()
          ->getRepository('DiscoveryeBookBundle:eBook')
          ->find($id);

        if ($ebook) {
            $form = $this->createForm(
              'Discovery\eBookBundle\Form\eBookDeleteType',
              $ebook,
              [
                'action' => '#',
              ]
            );

            $form->handleRequest($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->remove($ebook);
                $em->flush();

                return $this->redirect('/admin/ebooks');
            }
        }

        return array(
          'ebook' => $ebook,
          'form' => $form->createView(),
        );
    }
}

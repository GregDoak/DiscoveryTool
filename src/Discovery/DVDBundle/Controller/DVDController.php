<?php

namespace Discovery\DVDBundle\Controller;

use Discovery\DVDBundle\Entity\DVD;
use Discovery\DVDBundle\Form\DVDCreateType;
use Discovery\DVDBundle\Form\DVDDeleteType;
use Discovery\DVDBundle\Form\DVDUpdateType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class DVDController extends Controller
{
    /**
     * @Route("/admin/dvds")
     * @Template("@DiscoveryDVD/Default/index.html.twig")
     */
    public function dvdsIndexAction()
    {
        $data = [];

        $dvds = $this->getDoctrine()
          ->getRepository('DiscoveryDVDBundle:DVD')
          ->findAll();

        foreach ($dvds as $dvd) {
            $errors = $this->getDoctrine()->getRepository(
              'DiscoveryErrorBundle:Error'
            )->findBy(
              [
                'baseTable' => 'DVDS',
                'baseTableID' => $dvd->getImdbId(),
              ]
            );

            $data[] = [
              'imdbId' => $dvd->getImdbId(),
              'createdOn' => $dvd->getCreatedOn(),
              'updatedOn' => $dvd->getUpdatedOn(),
              'processed' => $dvd->getProcessed(),
              'attemptCount' => $dvd->getAttemptCount(),
              'errors' => sizeof($errors),
            ];
        }

        return array(
          'dvds' => $data,
        );
    }

    /**
     * @Route("/admin/dvd/create")
     * @Template("@DiscoveryDVD/Default/create.html.twig")
     */
    public function dvdCreateAction(Request $request)
    {
        $dvd = new DVD();

        $form = $this->createForm(
          'Discovery\DVDBundle\Form\DVDCreateType',
          $dvd,
          [
            'action' => '#',
          ]
        );

        $form->handleRequest($request);

        if ($form->isValid()) {
            $dvd->setCreatedOnValue();
            $dvd->setProcessedValue();
            $dvd->setAttemptCountValue();
            $em = $this->getDoctrine()->getManager();
            $em->persist($dvd);
            $em->flush();

            return $this->redirect('/admin/dvds');
        }

        return array(
          'dvd' => $dvd,
          'form' => $form->createView(),
        );

    }

    /**
     * @Route("/admin/dvd/update/{id}")
     * @Template("@DiscoveryDVD/Default/update.html.twig")
     */
    public function dvdUpdateAction($id)
    {
        $request = $this->container->get('request');

        $dvd = $this->getDoctrine()
          ->getRepository('DiscoveryDVDBundle:DVD')
          ->find($id);

        if ($dvd) {
            $form = $this->createForm(
              'Discovery\DVDBundle\Form\DVDUpdateType',
              $dvd,
              [
                'action' => '#',
              ]
            );

            $form->handleRequest($request);

            if ($form->isValid()) {
                $dvd->setProcessedValue();
                $dvd->setAttemptCountValue();
                $em = $this->getDoctrine()->getManager();
                $em->persist($dvd);
                $em->flush();
                $message = "DVD successfully updated.";
            }
        }

        return array(
          'dvd' => $dvd,
          'form' => $form->createView(),
          'message' => (isset($message)) ? $message : '',
        );
    }

    /**
     * @Route("/admin/dvd/delete/{id}")
     * @Template("@DiscoveryDVD/Default/delete.html.twig")
     */
    public function dvdDeleteAction($id)
    {
        $request = $this->container->get('request');

        $dvd = $this->getDoctrine()
          ->getRepository('DiscoveryDVDBundle:DVD')
          ->find($id);

        if ($dvd) {
            $form = $this->createForm(
              'Discovery\DVDBundle\Form\DVDDeleteType',
              $dvd,
              [
                'action' => '#',
              ]
            );

            $form->handleRequest($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->remove($dvd);
                $em->flush();

                return $this->redirect('/admin/dvds');
            }
        }

        return array(
          'dvd' => $dvd,
          'form' => $form->createView(),
        );
    }
}

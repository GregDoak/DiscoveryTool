<?php

namespace Discovery\BookBundle\Controller;

use Discovery\BookBundle\Entity\Book;
use Discovery\BookBundle\Form\BookCreateType;
use Discovery\BookBundle\Form\BookDeleteType;
use Discovery\BookBundle\Form\BookUpdateType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class BookController extends Controller
{
    /**
     * @Route("/admin/books")
     * @Template("@DiscoveryBook/Default/index.html.twig")
     */
    public function booksIndexAction()
    {
        $data = [];

        $books = $this->getDoctrine()
          ->getRepository('DiscoveryBookBundle:Book')
          ->findAll();

        foreach ($books as $book) {
            $errors = $this->getDoctrine()->getRepository(
              'DiscoveryErrorBundle:Error'
            )->findBy(
              [
                'baseTable' => 'BOOKS',
                'baseTableID' => $book->getIsbn(),
              ]
            );

            $data[] = [
              'isbn' => $book->getIsbn(),
              'googleUid' => $book->getGoogleUID(),
              'createdOn' => $book->getCreatedOn(),
              'updatedOn' => $book->getUpdatedOn(),
              'processed' => $book->getProcessed(),
              'attemptCount' => $book->getAttemptCount(),
              'errors' => sizeof($errors),
            ];
        }

        return array(
          'books' => $data,
        );
    }

    /**
     * @Route("/admin/book/create")
     * @Template("@DiscoveryBook/Default/create.html.twig")
     */
    public function bookCreateAction(Request $request)
    {
        $book = new Book();

        $form = $this->createForm(
          'Discovery\BookBundle\Form\BookCreateType',
          $book,
          [
            'action' => '#',
          ]
        );

        $form->handleRequest($request);

        if ($form->isValid()) {
            $book->setCreatedOnValue();
            $book->setProcessedValue();
            $book->setAttemptCountValue();
            $em = $this->getDoctrine()->getManager();
            $em->persist($book);
            $em->flush();

            return $this->redirect('/admin/books');
        }

        return array(
          'book' => $book,
          'form' => $form->createView(),
        );

    }

    /**
     * @Route("/admin/book/update/{id}")
     * @Template("@DiscoveryBook/Default/update.html.twig")
     */
    public function bookUpdateAction($id)
    {
        $request = $this->container->get('request');

        $book = $this->getDoctrine()
          ->getRepository('DiscoveryBookBundle:Book')
          ->find($id);

        if ($book) {
            $form = $this->createForm(
              'Discovery\BookBundle\Form\BookUpdateType',
              $book,
              [
                'action' => '#',
              ]
            );

            $form->handleRequest($request);

            if ($form->isValid()) {
                $book->setProcessedValue();
                $book->setAttemptCountValue();
                $em = $this->getDoctrine()->getManager();
                $em->persist($book);
                $em->flush();
                $message = "Book successfully updated.";
            }
        }

        return array(
          'book' => $book,
          'form' => $form->createView(),
          'message' => (isset($message)) ? $message : '',
        );
    }

    /**
     * @Route("/admin/book/delete/{id}")
     * @Template("@DiscoveryBook/Default/delete.html.twig")
     */
    public function bookDeleteAction($id)
    {
        $request = $this->container->get('request');

        $book = $this->getDoctrine()
          ->getRepository('DiscoveryBookBundle:Book')
          ->find($id);

        if ($book) {
            $form = $this->createForm(
              'Discovery\BookBundle\Form\BookDeleteType',
              $book,
              [
                'action' => '#',
              ]
            );

            $form->handleRequest($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->remove($book);
                $em->flush();

                return $this->redirect('/admin/books');
            }
        }

        return array(
          'book' => $book,
          'form' => $form->createView(),
        );
    }
}

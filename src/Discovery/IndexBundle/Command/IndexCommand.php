<?php

namespace Discovery\IndexBundle\Command;

use Discovery\BookBundle\Entity\Book;
use Discovery\ErrorBundle\Entity\Error;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class IndexCommand extends ContainerAwareCommand
{
    private $googleSearchUrl = "https://www.googleapis.com/books/v1/volumes?q=isbn:";
    private $googleSelfLink = "https://www.googleapis.com/books/v1/volumes/";

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
          ->setName('Discovery:Index')
          ->setDescription(
            'Indexing Service that takes the database item types and puts the information into Solr'
          )
          ->addOption(
            'index',
            'i',
            InputOption::VALUE_OPTIONAL,
            'Defaults to ALL but accepts BOOKS, EBOOKS, and DVDS for specific indexing'
          );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $index = (!(empty($input->getOption('index')))) ? strtoupper(
          $input->getOption('index')
        ) : 'ALL';

        switch ($index) {
            case "ALL":
                $output->writeln("Processing ".$index." item types...");
                break;
            case "BOOKS":
                $output->writeln("Processing ".$index." item type...");
                $this->indexBooks($output);
                break;
            case "DVDS":
                $output->writeln("Processing ".$index." item type...");
                break;
            case "EBOOKS":
                $output->writeln("Processing ".$index." item type...");
                break;
            default:
                $output->writeln(
                  'Unable to find "'.$index.'".  Accepted indexes are ALL, BOOKS, EBOOKS, and DVDS'
                );
        }
    }

    protected function indexBooks(OutputInterface $output)
    {
        $doctrine = $this->getContainer()->get('doctrine');
        $queryBuilder = $doctrine->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('book')
          ->from('DiscoveryBookBundle:Book', 'book')
          ->where('book.processed = 0')
          ->andWhere('book.attemptCount < 5');

        $query = $queryBuilder->getQuery();
        $books = $query->getResult();

        $processed = new \stdClass();
        $processed->total = sizeof($books);
        $processed->count = 0;
        $processed->indexed = 0;
        $processed->failed = 0;

        if ($processed->total > 0) {
            $output->writeln("Processing ".$processed->total." Books...");
            foreach ($books as $book) {
                /* @var $book Book */
                $processed->count++;

                try {
                    if (empty($book->getGoogleUID())) {
                        //get results from Google API
                        $curl = curl_init(
                          $this->googleSearchUrl.$book->getIsbn()
                        );
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                        $response = curl_exec($curl);

                        $isbnSearchResponse = json_decode($response);

                        if (!(isset($isbnSearchResponse->totalItems)) || ($isbnSearchResponse->totalItems == 0)) {
                            throw new \Exception(
                              "Unable to find ISBN from Google", 500
                            );
                        }

                        $isbnSearch = $isbnSearchResponse->items[0];

                        if ($isbnSearchResponse->totalItems == 1) {
                            $book->setGoogleUID($isbnSearch->id);
                        } else {
                            if (isset($isbnSearch->volumeInfo->industryIdentifiers)) {
                                foreach ($isbnSearch->volumeInfo->industryIdentifiers as $industryIdentifier) {
                                    if ($industryIdentifier->identifier == $book->getIsbn(
                                      )
                                    ) {
                                        $book->setGoogleUID($isbnSearch->id);
                                    }
                                }
                            }
                        }

                        if (empty($book->getGoogleUID())) {
                            throw new \Exception(
                              "Cannot find self link in Google results", 500
                            );
                        }
                    }

                    //get results from Google SelfLink
                    $curl = curl_init(
                      $this->googleSelfLink.$book->getGoogleUID()
                    );
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                    $response = curl_exec($curl);

                    $selfLinkResponse = json_decode($response);

                    if (!(isset($selfLinkResponse->selfLink))) {
                        throw new \Exception(
                          "Unable to record from Google self link", 500
                        );
                    }

                    print_r($selfLinkResponse);

                    $record = new \stdClass();
                    if (isset($selfLinkResponse->volumeInfo->authors)) {
                        $record->authors = $selfLinkResponse->volumeInfo->authors;
                    }
                    if (isset($selfLinkResponse->volumeInfo->categories)) {
                        $record->categories = $selfLinkResponse->volumeInfo->categories;
                    }
                    $record->collections = ['All Library Resources', 'Books'];
                    $record->googleUID = $selfLinkResponse->id;
                    $record->googleURL = $selfLinkResponse->selfLink;
                    $record->id = "BOOK: ".$book->getIsbn();
                    if ((isset($selfLinkResponse->accessInfo->accessViewStatus)) && ($selfLinkResponse->accessInfo->accessViewStatus != "NONE")) {
                        $record->linkType = $selfLinkResponse->accessInfo->accessViewStatus;
                    }
                    $record->opacURL = $book->getOpacURL();
                    if (isset($selfLinkResponse->volumeInfo->publisher)) {
                        $record->publisher = $selfLinkResponse->volumeInfo->publisher;
                    }
                    if (isset($selfLinkResponse->volumeInfo->publishedDate)) {
                        $record->publishedDate = $selfLinkResponse->volumeInfo->publishedDate;
                    }
                    if (isset($selfLinkResponse->volumeInfo->subtitle)) {
                        $record->subtitle = $selfLinkResponse->volumeInfo->subtitle;
                    }
                    if (isset($selfLinkResponse->volumeInfo->description)) {
                        $record->summary = strip_tags(
                          $selfLinkResponse->volumeInfo->description
                        );
                    }
                    if ((isset($selfLinkResponse->volumeInfo->imageLinks)) && (isset($selfLinkResponse->volumeInfo->imageLinks->thumbnail))) {
                        $record->thumbnail = $selfLinkResponse->volumeInfo->imageLinks->thumbnail;
                    }
                    $record->title = $selfLinkResponse->volumeInfo->title;
                    if ((isset($record->linkType)) && (isset($selfLinkResponse->accessInfo->webReaderLink))) {
                        $record->webReaderURL = $selfLinkResponse->accessInfo->webReaderLink;
                    }

                    $host = $this->getContainer()->getParameter('solr_host');
                    $port = $this->getContainer()->getParameter('solr_port');
                    $instance = $this->getContainer()->getParameter(
                      'solr_instance'
                    );

                    $url = "http://".$host.":".$port."/solr/".$instance."/update?overwrite=true&wt=json&commitWithin=1000";

                    echo $url;
                    print_r(json_encode($record));

                    //open a new curl handler to send the data to Solr
                    $ch = curl_init($url);
                    //set the data as POST
                    curl_setopt($ch, CURLOPT_POST, 1);
                    //set the POST data type to be XML
                    curl_setopt(
                      $ch,
                      CURLOPT_HTTPHEADER,
                      array('Content-Type: application/json')
                    );
                    //assign the XML document created above to the POST value
                    curl_setopt(
                      $ch,
                      CURLOPT_POSTFIELDS,
                      [json_encode($record)]
                    );
                    //execute and get response from Solr
                    $response = curl_exec($ch);
                    //close connection as not needed
                    curl_close($ch);

                    print_r($response);

                    $processed->indexed++;


                } catch (\Exception $exception) {
                    $em = $doctrine->getEntityManager();

                    $error = new Error();

                    $error->setBaseTable("BOOKS");
                    $error->setBaseTableID($book->getIsbn());
                    $error->setMessage($exception->getMessage());
                    $error->setCreatedOnValue();

                    $em->persist($error);
                    $em->flush();

                    $processed->failed++;
                }


                /*                if (!(empty($book->getGoogleUID()))) {
                                    //try get info using id
                                    $processed->indexed++;
                                } else {
                                    $curl = curl_init($this->googleSearchUrl.$book->getIsbn());
                                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                                    $response = curl_exec($curl);

                                    $results = json_decode($response);

                                    $em = $doctrine->getEntityManager();
                                    $now = new \DateTime();
                                    $book->setUpdatedOn($now);
                                    $book->setAttemptCount($book->getAttemptCount()+1);
                                    $book->setProcessed(1);

                                    $em->persist($book);
                                    $em->flush();
                                    $processed->indexed++;

                                    $output->writeln('Processed: '.$processed->count.'/'.$processed->total);
                                    if ($processed->count < $processed->total) {
                                        sleep(2);
                                    }
                                }*/
            }
            $output->writeln('');
            $output->writeln("Indexed: ".$processed->indexed);
            $output->writeln("Failed: ".$processed->failed);
            if ($processed->total !== 0) {
                $output->writeln(
                  'Success Rate: '.round(
                    $processed->indexed / $processed->total * 100,
                    0
                  ).'%'
                );
            }
        } else {
            $output->writeln('Processed: 0 Books.');
        }


    }
}
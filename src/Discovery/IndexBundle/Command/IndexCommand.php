<?php

namespace Discovery\IndexBundle\Command;

use Discovery\BookBundle\Entity\Book;
use Discovery\DVDBundle\Entity\DVD;
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
                $this->indexFromGoogle('Book', $output);
                $this->indexFromGoogle('eBook', $output);
                $this->indexFromOMDB('DVD', $output);
                break;
            case "BOOKS":
                $output->writeln("Processing ".$index." item type...");
                $this->indexFromGoogle('Book', $output);
                break;
            case "DVDS":
                $output->writeln("Processing ".$index." item type...");
                $this->indexFromOMDB('DVD', $output);
                break;
            case "EBOOKS":
                $output->writeln("Processing ".$index." item type...");
                $this->indexFromGoogle('eBook', $output);
                break;
            default:
                $output->writeln(
                  'Unable to find "'.$index.'".  Accepted indexes are ALL, BOOKS, EBOOKS, and DVDS'
                );
        }
    }

    protected function indexFromGoogle($itemType, OutputInterface $output)
    {

        $function = "get".$itemType."s";

        $items = $this->$function();

        $processed = $this->initializeProcessed($items);

        if ($processed->total > 0) {
            $output->writeln(
              "Processing ".$processed->total." ".$itemType."s..."
            );
            foreach ($items as $item) {
                /* @var $item eBook */
                $processed->count++;

                try {
                    if (empty($item->getGoogleUID())) {
                        //get results from Google API

                        $curlOptions = [
                          'url' => $this->googleSearchUrl.$item->getIsbn(),
                          'curlOptions' => [
                            CURLOPT_RETURNTRANSFER => 1,
                          ],
                        ];

                        $isbnSearchResponse = json_decode(
                          $this->curl($curlOptions)
                        );

                        //check for results from google search
                        if (!(isset($isbnSearchResponse->totalItems)) || ($isbnSearchResponse->totalItems == 0)) {
                            throw new \Exception(
                              "Unable to find ISBN from Google", 500
                            );
                        }

                        $isbnSearch = $isbnSearchResponse->items[0];

                        //check result to find match
                        if ($isbnSearchResponse->totalItems == 1) {
                            //assume only 1 result is the actual result
                            $item->setGoogleUID($isbnSearch->id);
                        } else {
                            //if more than 1 result try match using the ISBN number
                            if (isset($isbnSearch->volumeInfo->industryIdentifiers)) {
                                foreach ($isbnSearch->volumeInfo->industryIdentifiers as $industryIdentifier) {
                                    if ($industryIdentifier->identifier == $item->getIsbn(
                                      )
                                    ) {
                                        $item->setGoogleUID($isbnSearch->id);
                                    }
                                }
                            }
                        }

                        if (empty($item->getGoogleUID())) {
                            throw new \Exception(
                              "Cannot find self link in Google results", 500
                            );
                        }
                    }

                    //get results from Google SelfLink

                    $curlOptions = [
                      'url' => $this->googleSelfLink.$item->getGoogleUID(),
                      'curlOptions' => [
                        CURLOPT_RETURNTRANSFER => 1,
                      ],
                    ];

                    $selfLinkResponse = json_decode($this->curl($curlOptions));

                    if (!(isset($selfLinkResponse->selfLink))) {
                        throw new \Exception(
                          "Unable to record from Google self link", 500
                        );
                    }

                    $record = new \stdClass();
                    if (isset($selfLinkResponse->volumeInfo->authors)) {
                        $record->authors = $selfLinkResponse->volumeInfo->authors;
                    }
                    if (isset($selfLinkResponse->volumeInfo->categories)) {
                        $record->categories = $selfLinkResponse->volumeInfo->categories;
                    } else {
                        if ((isset($isbnSearch)) && (isset($isbnSearch->volumeInfo->categories))) {
                            $record->categories = $isbnSearch->volumeInfo->categories;
                        }
                    }
                    $record->collections = [
                      'All Library Resources',
                      $itemType.'s',
                    ];
                    $record->googleUID = $selfLinkResponse->id;
                    $record->googleURL = $selfLinkResponse->selfLink;
                    $record->id = strtoupper($itemType).": ".$item->getIsbn();
                    if ((isset($selfLinkResponse->accessInfo->accessViewStatus)) && ($selfLinkResponse->accessInfo->accessViewStatus != "NONE")) {
                        $record->linkType = $selfLinkResponse->accessInfo->accessViewStatus;
                    }
                    $record->opacURL = $item->getOpacURL();
                    if (isset($selfLinkResponse->volumeInfo->publisher)) {
                        $record->publisher = $selfLinkResponse->volumeInfo->publisher;
                    }
                    if (isset($selfLinkResponse->volumeInfo->publishedDate)) {
                        $timestamp = strtotime(
                          $selfLinkResponse->volumeInfo->publishedDate


                        );
                        $record->publishedDate = date(
                          'Y-m-d H:i:s',
                          $timestamp
                        );
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

                    if ($itemType == "eBook") {
                        $record->webReaderURL = $item->getUrl();
                        $record->linkType = $item->getLinkTypeText();
                    }

                    $response = $this->postToSolr($record);

                    if (!(isset($response->responseHeader)) || ($response->responseHeader->status != 0)) {
                        print_r($response);
                        throw new \Exception(
                          "Unable to save record into Solr",
                          500
                        );
                    }

                    $processed->indexed++;

                    $em = $this->getContainer()->get('doctrine')->getManager();
                    $item->setGoogleUID($record->googleUID);
                    $item->setAttemptCount($item->getAttemptCount() + 1);
                    $item->setUpdatedOn(new \DateTime());
                    $item->setProcessed(1);

                    $em->persist($item);
                    $em->flush();

                } catch (\Exception $exception) {
                    $em = $this->getContainer()->get('doctrine')->getManager();

                    $error = new Error();

                    $error->setBaseTable(strtoupper($itemType."S"));
                    $error->setBaseTableID($item->getIsbn());
                    $error->setMessage($exception->getMessage());
                    $error->setCreatedOnValue();

                    $item->setAttemptCount($item->getAttemptCount() + 1);

                    $em->persist($error);
                    $em->persist($item);
                    $em->flush();

                    $processed->failed++;
                }
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
            $output->writeln('Processed: 0 '.$itemType.'s.');
        }
    }

    protected function initializeProcessed($items)
    {
        $processed = new \stdClass();
        $processed->total = sizeof($items);
        $processed->count = 0;
        $processed->indexed = 0;
        $processed->failed = 0;

        return $processed;
    }

    protected function curl($options)
    {
        $curl = curl_init($options['url']);
        if (isset($options['curlOptions'])) {
            foreach ($options['curlOptions'] as $option => $value) {
                //echo $option."--".$value;
                curl_setopt($curl, $option, $value);
            }
        }

        return curl_exec($curl);
    }

    protected function postToSolr($record)
    {
        $host = $this->getContainer()->getParameter('solr_host');
        $port = $this->getContainer()->getParameter('solr_port');
        $instance = $this->getContainer()->getParameter(
          'solr_instance'
        );

        $url = "http://".$host.":".$port."/solr/".$instance."/update?overwrite=true&wt=json&commitWithin=1000";

        $curlOptions = [
          'url' => $url,
          'curlOptions' => [
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POST => 1,
            CURLOPT_HTTPHEADER => [
              'Content-Type: application/json',
              'Accept: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode([$record]),
          ],
        ];

        return json_decode($this->curl($curlOptions));
    }

    protected function indexFromOMDB($itemType, OutputInterface $output)
    {
        $function = "get".$itemType."s";

        $items = $this->$function();

        $processed = $this->initializeProcessed($items);

        if ($processed->total > 0) {
            $output->writeln(
              "Processing ".$processed->total." ".$itemType."s..."
            );
            foreach ($items as $item) {
                /* @var $item DVD */
                $processed->count++;

                try {

                    //get results from Google SelfLink

                    $curlOptions = [
                      'url' => "http://www.omdbapi.com/?i=".$item->getImdbId(),
                      'curlOptions' => [
                        CURLOPT_RETURNTRANSFER => 1,
                      ],
                    ];

                    $selfLinkResponse = json_decode($this->curl($curlOptions));

                    if (!(isset($selfLinkResponse->Response))) {
                        throw new \Exception(
                          "Unable to record from self link", 500
                        );
                    }

                    $record = new \stdClass();
                    if (isset($selfLinkResponse->Title)) {
                        $record->title = $selfLinkResponse->Title;
                    }
                    if (isset($selfLinkResponse->Released)) {
                        $timestamp = strtotime($selfLinkResponse->Released);
                        $record->publishedDate = date(
                          'Y-m-d H:i:s',
                          $timestamp
                        );
                    }
                    if (isset($selfLinkResponse->Genre)) {
                        $record->categories = explode(
                          ",",
                          $selfLinkResponse->Genre
                        );
                    }
                    if (isset($selfLinkResponse->Director)) {
                        $record->publisher = $selfLinkResponse->Director;
                    }
                    if (isset($selfLinkResponse->Plot)) {
                        $record->summary = $selfLinkResponse->Plot;
                    }
                    if (isset($selfLinkResponse->Poster)) {
                        $record->thumbnail = $selfLinkResponse->Poster;
                    }
                    if (isset($selfLinkResponse->Actors)) {
                        $record->authors = explode(
                          ",",
                          $selfLinkResponse->Actors
                        );
                    }
                    $record->collections = [
                      'All Library Resources',
                      $itemType.'s',
                    ];
                    $record->id = strtoupper($itemType).": ".$item->getImdbId();
                    $record->opacURL = $item->getOpacURL();

                    $response = $this->postToSolr($record);

                    if (!(isset($response->responseHeader)) || ($response->responseHeader->status != 0)) {
                        throw new \Exception(
                          "Unable to save record into Solr",
                          500
                        );
                    }

                    $processed->indexed++;

                    $em = $this->getContainer()->get('doctrine')->getManager();
                    $item->setAttemptCount($item->getAttemptCount() + 1);
                    $item->setUpdatedOn(new \DateTime());
                    $item->setProcessed(1);

                    $em->persist($item);
                    $em->flush();

                } catch (\Exception $exception) {
                    $em = $this->getContainer()->get('doctrine')->getManager();

                    $error = new Error();

                    $error->setBaseTable(strtoupper($itemType."S"));
                    $error->setBaseTableID($item->getImdbId());
                    $error->setMessage($exception->getMessage());
                    $error->setCreatedOnValue();

                    $item->setAttemptCount($item->getAttemptCount() + 1);

                    $em->persist($error);
                    $em->persist($item);
                    $em->flush();

                    $processed->failed++;
                }
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
            $output->writeln('Processed: 0 '.$itemType.'s.');
        }
    }

    protected function getBooks()
    {
        $doctrine = $this->getContainer()->get('doctrine');
        $queryBuilder = $doctrine->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('book')
          ->from('DiscoveryBookBundle:Book', 'book')
          ->where('book.processed = 0')
          ->andWhere('book.attemptCount < 5');

        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }

    protected function getDVDs()
    {
        $doctrine = $this->getContainer()->get('doctrine');
        $queryBuilder = $doctrine->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('dvd')
          ->from('DiscoveryDVDBundle:DVD', 'dvd')
          ->where('dvd.processed = 0')
          ->andWhere('dvd.attemptCount < 5');

        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }

    protected function geteBooks()
    {
        $doctrine = $this->getContainer()->get('doctrine');
        $queryBuilder = $doctrine->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('ebook')
          ->from('DiscoveryeBookBundle:eBook', 'ebook')
          ->where('ebook.processed = 0')
          ->andWhere('ebook.attemptCount < 5');

        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }
}
<?php

namespace Discovery\ThumbnailBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ThumbnailController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction(Request $request)
    {

        $fileSystem = new Filesystem();

        $cacheDirectory = $this->getCacheDir();

        if (!$fileSystem->exists($cacheDirectory)) {
            $fileSystem->mkdir($cacheDirectory, 0775);
        }

        $file = $request->get('file');

        $cachedFile = $cacheDirectory.md5($file).".txt";

        if ($fileSystem->exists($cachedFile)) {
            $data = file_get_contents($cachedFile);
        } else {
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

            try {
                $fileSystem->dumpFile($cachedFile, $data, 0775);
            } catch (IOException $e) {
                echo $e->getMessage();
            }

        }

        header('Content-type: image/jpeg');
        $image = \imagecreatefromstring($data);


        imagejpeg($image, null, 100);

        return new Response();
    }

    public function getCacheDir()
    {
        return $this->get('kernel')->getRootDir().'/cache/'.$this->get('kernel')
          ->getEnvironment().'/images/';
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 4/29/19
 * Time: 11:18 AM
 */

namespace Edgar\Tools;

use Symfony\Component\Serializer\Encoder\XmlEncoder;

class DocumentLoader
{
    protected $contents;
    const STORAGE_DIR = __DIR__ . "/.storage/";

    /**
     * @param $url
     * @param bool $saveOnLoad
     * @return mixed
     * @throws \Exception
     */
    public function load($url, $saveOnLoad = true)
    {
        $contents = $this->getContents($url, $saveOnLoad);
        return (new XmlEncoder())->decode($contents, 'xml');
    }

    public function read()
    {
        return $this->contents;
    }

    public function remap($func, $data = null)
    {
        $data = (!empty($data)) ? $data : $this->contents;
        return array_map($func, $data);
    }

    public function stashContent($contents, $path)
    {
        if (!file_exists($path)) {
            file_put_contents($path, $contents);
        }
        return true;
    }

    public function verifyStorageDir()
    {
        if (!is_dir(self::STORAGE_DIR)) {
            return mkdir(self::STORAGE_DIR, 0700);
        }
        return true;
    }

    /**
     * @param $url
     * @param $saveOnLoad
     * @return bool|string
     * @throws \Exception
     */
    public function getContents($url, $saveOnLoad)
    {
        if (!$saveOnLoad) {
            return $this->curl_get_contents($url);
        }
        if ($this->verifyStorageDir()) {
            $filename = self::STORAGE_DIR . basename($url);
            if (!file_exists($filename)) {
                $contents = $this->curl_get_contents($url);
                $this->stashContent($contents, $filename);
            }
            return file_get_contents($filename);
        }
        throw new \Exception("Failed to verify that the storage dir exists, or failed to create dir");
    }

    public function curl_get_contents($url)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36',
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
}

<?php

namespace App\Service\DataSources;

use App\Entity\Asset;
use App\Entity\AssetPrice;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Download stock data from alphavantage.co API
 * To gain access to the API, you need to define your API key in the .env.local file (e.g. ALPHAVANTAGE_KEY=12345)
 * 
 * See: https://www.alphavantage.co/documentation/
 */
class Alphavantage implements DataSourceInterface
{
    private string $apikey;

    public function __construct(
        private HttpClientInterface $client
    ) {
        $this->apikey = $_ENV["ALPHAVANTAGE_KEY"];
    }

    public function isAvailable() : bool
    {
        return !empty($this->apikey);
    }

    public function getName() : string
    {
        return "AlphaVantage";
    }

    public function supports(Asset $asset) : bool
    {
        if (!$this->isAvailable())
        {
            return false;
        }
        $pds = $asset->getPriceDataSource();
        $pattern = "/^av\/.*/i";
        return preg_match($pattern, $pds);
    }

    protected function getSymbol(Asset $asset) : string
    {
        $expr = $asset->getPriceDataSource();
        $prefix = "AV/";
        return substr($expr, strlen($prefix));
    }

    public function getPrices(Asset $asset, \DateTimeInterface $startdate, \DateTimeInterface $enddate) : array
    {
        if (!$this->isAvailable())
        {
            throw new \RuntimeException("No API key defined. Define ALPHAVANTAGE_KEY in your local .env file.");
        }

        $symbol = $this->getSymbol($asset);
        $url = "https://www.alphavantage.co/query";
        $query = [
            'function' => "TIME_SERIES_DAILY",
            'symbol' => $symbol,
            'outputsize' => 'compact',
            'datatype' => "csv",
            'apikey' => $this->apikey,
        ];

        $response = $this->client->request('GET', $url, [
            'query' => $query
        ]);
        if ($response->getStatusCode() != 200)
        {
            $code = $response->getStatusCode();
            throw new \RuntimeException("Failed to retrieve prices (Error code $code)");
        }

        $content_type = $response->getHeaders()['content-type'][0];
        $content = $response->getContent();
        if ($content_type == 'application/json')
        {
            //$error = json_decode($content); // TODO: Decode
            //var_dump($error);
            throw new \RuntimeException("Failed to retrieve prices ($content)");
        }
        
        $rows = str_getcsv($content, "\n");

        $ret = [];
        $keys = [];
        foreach ($rows as $line)
        {
            $fields = str_getcsv($line);
            if (empty($keys))
            {
                $keys = $fields;
                continue;
            }

            $fields = array_combine($keys, $fields);

            try
            {
                $date = \DateTime::createFromFormat('Y-m-d H:i:s', $fields['timestamp'] . " 00:00:00");

                if ($date < $startdate || $date > $enddate)
                {
                    continue;
                }

                $popen = $fields['open'];
                $phigh = $fields['high'];
                $plow = $fields['low'];
                $pclose = $fields['close'];
                $volume = array_key_exists('volume', $fields) ? $fields['volume'] : 0;

                $ap = new AssetPrice();
                $ap->setAsset($asset);
                $ap->setDate($date);
                $ap->setOHLC($popen, $phigh, $plow, $pclose);
                $ap->setVolume($volume);

                $ret[] = $ap;
            }
            catch (\Exception $ex)
            {
                throw new \RuntimeException("Found line with invalid format: $line");
            }
        }

        return $ret;
    }
}

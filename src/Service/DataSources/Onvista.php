<?php

namespace App\Service\DataSources;

use App\Entity\Asset;
use App\Entity\AssetPrice;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Download stock data from onvista.de
 * 
 * See: https://github.com/cloasdata/pyonvista for a python implementation
 */
class Onvista implements DataSourceInterface
{
    private const ONVISTA_API_BASE = "https://api.onvista.de/api/v1/";

    public function __construct(
        private HttpClientInterface $client
    ) {
    }

    public function isAvailable() : bool
    {
        return true;
    }

    public function getName() : string
    {
        return "Onvista";
    }

    public function getPrefix() : string
    {
        return "OV";
    }

    public function supports(Asset $asset) : bool
    {
        $pds = $asset->getPriceDataSource();
        $pattern = "/^ov\/\d+/i";
        if (!preg_match($pattern, $pds))
        {
            return false;
        }
        try
        {
            $type = $this->getType($asset);
            return true;
        }
        catch (\Exception $ex)
        {
            return false;
        }
    }

    protected function getId(Asset $asset) : int
    {
        $expr = $asset->getPriceDataSource();
        $prefix = $this->getPrefix() . "/";
        return intval(substr($expr, strlen($prefix)));
    }

    protected function getType(Asset $asset) : string
    {
        switch ($asset->getType())
        {
            case Asset::TYPE_STOCK:
                return "STOCK";
            
            case Asset::TYPE_INDEX:
                return "INDEX";
            
            case Asset::TYPE_FUND:
                return "FUND";

            // TODO: add remaining types

            default:
                throw new \RuntimeException("Unsupported asset type: " . $asset->getTypeName());
        }
    }

    public function getPrices(Asset $asset, \DateTimeInterface $startdate, \DateTimeInterface $enddate) : array
    {
        $id = $this->getId($asset);
        $type = $this->getType($asset);
        $url = Onvista::ONVISTA_API_BASE . "instruments/$type/$id/chart_history";
        $query = [
            'resolution' => "1D",
            'startDate' => $startdate->format('Y-m-d'),
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
        if ($content_type != 'application/json')
        {
            throw new \RuntimeException("Failed to retrieve prices ($content)");
        }
        
        $data = json_decode($content);
        
        $currency = $data->{"isoCurrency"};
        if ($currency != $asset->getCurrency())
        {
            throw new \RuntimeException("Currency mismatch. Expected " . $asset->getCurrency() . " and received $currency");
        }
        $datetime = $data->{"datetimeLast"};
        $open = $data->{"first"};
        $high = $data->{"high"};
        $low = $data->{"low"};
        $close = $data->{"last"};
        $volume = $data->{"volume"};

        $num_elements = count($datetime);
        if (count($open) != $num_elements ||
            count($high) != $num_elements ||
            count($low) != $num_elements ||
            count($close) != $num_elements ||
            count($volume) != $num_elements)
        {
            throw new \RuntimeException("Inconsitent results received");        
        }

        $map_fn = function($d, $o, $h, $l, $c, $v) use ($asset)
        {
            $ap = new AssetPrice();
            $ap->setAsset($asset);
            $ap->setDate(\DateTime::createFromFormat('U', $d));
            $ap->setOHLC($o, $h, $l, $c);
            $ap->setVolume($v);
            return $ap;
        };
        $filter_fn = function($ap) use ($startdate, $enddate)
        {
            return $ap->getDate() >= $startdate && $ap->getDate() <= $enddate;
        };
        $prices = array_filter(array_map($map_fn, $datetime, $open, $high, $low, $close, $volume), $filter_fn);
        return $prices;
    }
}

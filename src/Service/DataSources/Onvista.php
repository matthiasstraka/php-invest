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

    public function supports(Asset $asset) : bool
    {
        try
        {
            $config = $this->getConfig($asset);
            if (!$config)
            {
                return false;
            }
            if ($config['provider'] != "onvista")
            {
                return false;
            }
            $type = array_key_exists('type', $config) ? $config["type"] : $this->getType($asset);
            if (empty($type))
            {
                return false;
            }
            return true;
        }
        catch (\Exception $ex)
        {
            return false;
        }
    }

    protected function getConfig(Asset $asset)
    {
        return json_decode($asset->getPriceDataSource(), true);
    }

    protected function getType(Asset $asset) : string
    {
        switch ($asset->getType())
        {
            case Asset::TYPE_STOCK:
                return "STOCK";
            case Asset::TYPE_BOND:
                return "BOND";
            case Asset::TYPE_FX:
                return "CURRENCY";
            case Asset::TYPE_COMMODITY:
                return "PRECIOUS_METAL";
            case Asset::TYPE_INDEX:
                return "INDEX";
            case Asset::TYPE_FUND:
                return "FUND";
            case Asset::TYPE_CRYPTO:
                return "CRYPTO";
            default:
                throw new \RuntimeException("Unsupported asset type: " . $asset->getTypeName());
        }
    }

    public function getPrices(Asset $asset, \DateTimeInterface $startdate, \DateTimeInterface $enddate) : array
    {
        $config = $this->getConfig($asset);
        $type = array_key_exists('type', $config) ? $config["type"] : $this->getType($asset);

        assert($config['provider'] == "onvista");
        if (!array_key_exists('idInstrument', $config))
        {
            throw new \RuntimeException("Onvista requires idInstrument to be set");
        }
        $id = $config['idInstrument'];

        $url = Onvista::ONVISTA_API_BASE . "instruments/$type/$id/chart_history";
        $query = [
            'resolution' => "1D",
            'startDate' => $startdate->format('Y-m-d'),
        ];
        if (array_key_exists('idNotation', $config))
        {
            $query['idNotation'] = $config['idNotation'];
        }
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

        if (array_key_exists('scale', $config))
        {
            $scale = strval($config["scale"]);
            $map_fn = function($d, $o, $h, $l, $c, $v) use ($asset, $scale)
            {
                $ap = new AssetPrice();
                $ap->setAsset($asset);
                $ap->setDate(\DateTime::createFromFormat('U', $d));
                $ap->setOHLC(
                    bcmul($o, $scale, 4),
                    bcmul($h, $scale, 4),
                    bcmul($l, $scale, 4),
                    bcmul($c, $scale, 4));
                $ap->setVolume($v);
                return $ap;
            };
        }
        else
        {
            $map_fn = function($d, $o, $h, $l, $c, $v) use ($asset)
            {
                $ap = new AssetPrice();
                $ap->setAsset($asset);
                $ap->setDate(\DateTime::createFromFormat('U', $d));
                $ap->setOHLC($o, $h, $l, $c);
                $ap->setVolume($v);
                return $ap;
            };
        }
        $filter_fn = function($ap) use ($startdate, $enddate)
        {
            return $ap->getDate() >= $startdate && $ap->getDate() <= $enddate;
        };
        $prices = array_filter(array_map($map_fn, $datetime, $open, $high, $low, $close, $volume), $filter_fn);
        return $prices;
    }
}

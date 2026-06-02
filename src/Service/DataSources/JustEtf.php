<?php

namespace App\Service\DataSources;

use App\Entity\Asset;
use App\Entity\AssetPrice;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Download ETF data from justetf.com
 * JustETF uses ISIN numbers but only supplied daily close prices
 */
class JustEtf implements DataSourceInterface
{
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
        return "Just ETF";
    }

    public function supports(Asset $asset) : bool
    {
        $datasource = $asset->getPriceDataSource();
        return strcasecmp($datasource, "JE") == 0
            || strcasecmp($datasource, "JustETF") == 0;
    }

    public function getPrices(Asset $asset, \DateTimeInterface $startdate, \DateTimeInterface $enddate) : array
    {
        $isin = $asset->getISIN();

        $url = "https://www.justetf.com/api/etfs/$isin/performance-chart";
        $query = [
            'locale' => 'en',
            'currency' => $asset->getCurrency(),
            'valuesType' => 'MARKET_VALUE',
            'reduceData' => 'false',
            'includeDividends' => 'false',
            'dateFrom' => $startdate->format('Y-m-d'), // e.g. '2025-09-25',
            'dateTo' => $enddate->format('Y-m-d'),
        ];
        
        $response = $this->client->request('GET', $url, [
            'query' => $query,
        ]);
        if ($response->getStatusCode() != 200)
        {
            $code = $response->getStatusCode();
            throw new \RuntimeException("Failed to retrieve prices (Error code $code)");
        }

        $content_type = $response->getHeaders()['content-type'][0];
        if (!str_starts_with($content_type, "application/json"))
        {
            throw new \RuntimeException("Unexpected content type: $content_type");
        }
        $data = json_decode($response->getContent(), true);

        $series = $data["series"];
        $ret = [];
        foreach ($series as $p)
        {
            try
            {
                $date = \DateTime::createFromFormat('Y-m-d H:i:s', $p['date'] . " 00:00:00");
                $pclose = $p["value"]["localized"];

                $ap = new AssetPrice();
                $ap->setAsset($asset);
                $ap->setDate($date);
                $ap->setOHLC($pclose, $pclose, $pclose, $pclose);
                $ap->setVolume(0);

                $ret[] = $ap;
            }
            catch(\Exception $ex)
            {
                $d = json_encode($p);
                throw new \RuntimeException("Found line with invalid format: $d");
            }
        }

        return $ret;
    }
}

<?php

namespace App\Service\DataSources;

use App\Entity\Asset;
use App\Entity\AssetPrice;
use Symfony\Component\HttpClient\HttpClient;

class Marketwatch implements DataSourceInterface
{
    const INDEX_COUNTRYCODES = [
        'DE' => 'dx'
    ];
    const STOCK_COUNTRYCODES = [
        'DE' => 'xe',
        'AT' => 'at',
    ];
    public function getPrices(Asset $asset, \DateTimeInterface $startdate, \DateTimeInterface $enddate) : array
    {
        $isin_country = strtoupper(substr($asset->getISIN(), 0, 2));
        if ($asset->getType() == Asset::TYPE_INDEX) {
            $type = "index";
            $country_code = self::INDEX_COUNTRYCODES[$isin_country] ?? null;
        } elseif ($asset->getType() == Asset::TYPE_FX) {
            $type = "currency";
            $country_code = null;
        } else {
            $type = "stock";
            $country_code = self::STOCK_COUNTRYCODES[$isin_country] ?? null;
        }
        $ticker = strtolower($asset->getSymbol());
        $url = "https://www.marketwatch.com/investing/$type/$ticker/downloaddatapartial";
        $query = [
            'startdate' => $startdate->format('m/d/Y H:i:s'),// e.g. '09/15/2021 00:00:00',
            'enddate' => $enddate->format('m/d/Y H:i:s'),
            //'daterange' => 'd30',
            'frequency' => 'P1D',
            'csvdownload' => 'true',
            //'downloadpartial' => 'false',
            'newdates' => 'false'
        ];

        if ($country_code)
        {
            $query['countrycode'] = $country_code;
        }
        
        $client = HttpClient::create();
        $response = $client->request('GET', $url, ['query' => $query]);
        if ($response->getStatusCode() == 200)
        {
            $lines = explode("\n", $response->getContent());

            $ret = [];
            $keys = [];
            foreach ($lines as $line)
            {
                if (strlen($line) == 0)
                    continue;

                $fields = str_getcsv($line);
                if (empty($keys))
                {
                    $keys = $fields;
                    continue;
                }

                $fields = array_combine($keys, $fields);
                //var_dump($fields);

                try
                {
                    $date = \DateTime::createFromFormat('m/d/Y H:i:s', $fields['Date'] . " 00:00:00");
                    $popen = str_replace(",", "", $fields['Open']);
                    $phigh = str_replace(",", "", $fields['High']);
                    $plow = str_replace(",", "", $fields['Low']);
                    $pclose = str_replace(",", "", $fields['Close']);
                    $volume = array_key_exists('Volume', $fields) ? str_replace(",", "", $fields['Volume']) : 0;
    
                    $ap = new AssetPrice();
                    $ap->setAsset($asset);
                    $ap->setDate($date);
                    $ap->setOHLC($popen, $phigh, $plow, $pclose);
                    $ap->setVolume($volume);
    
                    $ret[] = $ap;
                }
                catch (Exception $ex)
                {
                    throw new \RuntimeException("Found line with invalid format: $line");
                }
            }
            return $ret;
        }
        else
        {
            $code = $response->getStatusCode();
            throw new \RuntimeException("Failed to retrieve prices (Error code $code)");
        }
    }
}

<?php

namespace App\Service\DataSources;

use App\Entity\Asset;
use App\Entity\AssetPrice;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Download stock data from marketwatch.com
 * Since MarketWatch uses ticker symbols, it is important to use the correct name
 * There is a mapping between ISIN numbers an market-watch country codes which is not complete but grows as needed
 * 
 * The list of stocks with country data can be found on https://www.marketwatch.com/tools/markets/stocks
 * 
 */
class Marketwatch implements DataSourceInterface
{
    public function __construct(
        private HttpClientInterface $client
    ) {
    }

    public function isAvailable() : bool
    {
        return true;
    }

    public function getPrices(Asset $asset, \DateTimeInterface $startdate, \DateTimeInterface $enddate) : array
    {
        switch ($asset->getType())
        {
            case Asset::TYPE_STOCK:
                $type = "stock";
                break;
            case Asset::TYPE_BOND:
                $type = "bond";
                break;
            case Asset::TYPE_FX:
                $type = "currency";
                break;
            case Asset::TYPE_COMMODITY:
                $type = "future";
                break;
            case Asset::TYPE_INDEX:
                $type = "index";
                break;
            case Asset::TYPE_FUND:
                $type = "fund";
                break;
            case Asset::TYPE_CRYPTO:
                $type = "cryptocurrency";
                break;
            default:
                throw new \RuntimeException("Unsupported asset type: " . $asset->getTypeName());
        }

        if ($asset->getMarketWatch())
        {
            // Read settings from the market watch field "(countrycode:)?ticker"
            $parts = explode(":", $asset->getMarketWatch());
            switch (count($parts))
            {
                case 1:
                    // aapl
                    $country_code = null;
                    $ticker = $parts[0];
                    break;
                case 2:
                    // xe:sie
                    $country_code = $parts[0];
                    $ticker = $parts[1];
                    break;
                case 2:
                    // future::gold
                    $type = $parts[0];
                    $country_code = $parts[1];
                    $ticker = $parts[2];
                    break;
                default:
                    throw new \RuntimeException("Invalid MarketWatch ticker definition: " . $asset->getMarketWatch());
            }
        } else {
            // Guess from ISIN/Symbol
            $country_code = null;
            if ($asset->getType() == Asset::TYPE_STOCK)
            {
                $isin_code = strtolower(substr($asset->getISIN(), 0, 2));
                if ($isin_code != "us")
                    $country_code = $isin_code;
            }
            
            $ticker = strtolower($asset->getSymbol());
        }

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
        
        $response = $this->client->request('GET', $url, [
            'query' => $query,
            'verify_host' => false, // workaround for "SSL: no alternative certificate subject name matches target host name" error
        ]);
        if ($response->getStatusCode() != 200)
        {
            $code = $response->getStatusCode();
            throw new \RuntimeException("Failed to retrieve prices (Error code $code)");
        }

        $lines = explode("\n", $response->getContent());

        $ret = [];
        $keys = [];
        foreach ($lines as $line)
        {
            if (!$line)
                continue;

            $fields = str_getcsv($line);
            if (empty($keys))
            {
                $keys = $fields;
                continue;
            }

            $fields = array_combine($keys, $fields);

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
            catch (\Exception $ex)
            {
                throw new \RuntimeException("Found line with invalid format: $line");
            }
        }
        return $ret;
    }
}

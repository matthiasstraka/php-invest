<?php

namespace App\Services\DataSources;

use App\Entity\Asset;
use Symfony\Component\HttpClient\HttpClient;

class Marketwatch
{
    public static function getPrices(?Asset $asset, \DateTimeInterface $startdate, \DateTimeInterface $enddate)
    {
        if ($asset == null)
        {
            throw new \InvalidArgumentException("Invalid asset");
        }

        if ($asset->getType() == Asset::TYPE_INDEX) {
            $type = "index";
        } else {
            $type = "stock";
        }
        $ticker = $asset->getSymbol();
        $url = "https://www.marketwatch.com/investing/$type/$ticker/downloaddatapartial";
        $query = [
            'startdate' => $startdate->format('m/d/Y H:i:s'),// e.g. '09/15/2021 00:00:00',
            'enddate' => $enddate->format('m/d/Y H:i:s'),
            //'daterange' => 'd30',
            'frequency' => 'P1D',
            'csvdownload' => 'true',
            //'downloadpartial' => 'false',
            //'newdates' => 'false',
        ];

        
        $client = HttpClient::create();
        $response = $client->request('GET', $url, ['query' => $query]);
        if ($response->getStatusCode() == 200)
        {
            $lines = explode("\n", $response->getContent());
            $ret = [];
            $has_header = false;
            foreach ($lines as $line)
            {
                if (strlen($line) == 0)
                    continue;

                $fields = str_getcsv($line);
                if (count($fields) >= 5)
                {
                    if ($fields[0] == 'Date')
                    {
                        $has_header = true;
                        continue;
                    }

                    if ($has_header)
                    {
                        $volume = (count($fields) >= 6) ? str_replace(",", "", $fields[5]) : 0;
                        $out = [
                            'Date' => \DateTime::createFromFormat('m/d/Y H:i:s', $fields[0] . " 00:00:00"),
                            'Open' => str_replace(",", "", $fields[1]),
                            'High' => str_replace(",", "", $fields[2]),
                            'Low' => str_replace(",", "", $fields[3]),
                            'Close' => str_replace(",", "", $fields[4]),
                            'Volume' => $volume,
                        ];

                        $ret[] = $out;
                    }
                }
                else
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

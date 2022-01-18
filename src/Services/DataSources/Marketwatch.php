<?php

namespace App\Services\DataSources;

use Symfony\Component\HttpClient\HttpClient;

class Marketwatch
{
    public static function getPrices(string $ticker, \DateTimeInterface $startdate, \DateTimeInterface $enddate)
    {
        $url = "https://www.marketwatch.com/investing/stock/$ticker/downloaddatapartial";
        $query = [
            'startdate' => $startdate->format('m/d/Y H:i:s'),// e.g. '09/15/2021 00:00:00',
            'enddate' => $enddate->format('m/d/Y H:i:s'),
            //'daterange' => 'd30',
            'frequency' => 'p1d',
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
                $fields = str_getcsv($line);
                if (count($fields) == 6)
                {
                    if ($fields[0] == 'Date')
                    {
                        $has_header = true;
                        continue;
                    }

                    if ($has_header)
                    {
                        $out = [
                            'Date' => \DateTime::createFromFormat('m/d/Y H:i:s', $fields[0] . " 00:00:00"),
                            'Open' => $fields[1],
                            'High' => $fields[2],
                            'Low' => $fields[3],
                            'Close' => $fields[4],
                            'Volume' => str_replace(",", "", $fields[5]),
                        ];

                        $ret[] = $out;
                    }
                }
            }
            return $ret;
        }
        else
        {
            return [];
        }
    }
}

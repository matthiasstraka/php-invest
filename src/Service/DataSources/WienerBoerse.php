<?php

namespace App\Service\DataSources;

use App\Entity\Asset;
use App\Entity\AssetPrice;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Download daily price data from wienerborse.at.
 */
class WienerBoerse implements DataSourceInterface
{
    private const DATASOURCE_REGEX = '/^(?:WB|WienerBoerse)\/(?<idNotation>\d+)$/i';
    private const API_URL = 'https://www.wienerborse.at/interactive-chart/canvas/data/';

    public function __construct(
        private HttpClientInterface $client
    ) {
    }

    public function isAvailable(): bool
    {
        return true;
    }

    public function getName(): string
    {
        return 'Wiener Börse';
    }

    public static function ParseDatasourceString(?string $datasource): ?array
    {
        if (!$datasource) {
            return null;
        }

        if (preg_match(self::DATASOURCE_REGEX, $datasource, $matches)) {
            $idNotation = (int) $matches['idNotation'];
            if ($idNotation <= 0) {
                return null;
            }

            return [
                'provider' => 'wienerboerse',
                'idNotation' => $idNotation,
            ];
        }

        $config = json_decode($datasource, true);
        if (!is_array($config)
            || !isset($config['provider'], $config['idNotation'])
            || !in_array($config['provider'], ['wienerboerse', 'WB'], true)
            || (!is_int($config['idNotation']) && !is_string($config['idNotation']))
            || !preg_match('/^\d+$/', (string) $config['idNotation'])
            || (int) $config['idNotation'] <= 0
        ) {
            return null;
        }

        $config['idNotation'] = (int) $config['idNotation'];

        return $config;
    }

    public function supports(Asset $asset): bool
    {
        return self::ParseDatasourceString($asset->getPriceDataSource()) !== null;
    }

    public function getPrices(Asset $asset, \DateTimeInterface $startdate, \DateTimeInterface $enddate): array
    {
        $config = self::ParseDatasourceString($asset->getPriceDataSource());
        if (!$config) {
            throw new \RuntimeException('Datasource not supported');
        }

        $utc = new \DateTimeZone('UTC');
        $formatDate = static fn (\DateTimeInterface $date): string =>
            \DateTimeImmutable::createFromInterface($date)->setTimezone($utc)->format('Y-m-d\TH:i:s.v\Z');

        $query = [
            'DATETIME_TZ_START_RANGE' => $formatDate($startdate),
            'DATETIME_TZ_END_RANGE' => $formatDate($enddate),
            'ID_NOTATION' => $config['idNotation'],
            'useExchanges' => 'false',
            'PRICE_TYPE' => 'LAST',
            'GRANULARITY' => '1D',
            'WITH_EARNINGS' => 0,
            'ID_QUALITY_PRICE' => 2,
            'MAX_HISTORY_YEARS' => 36,
            'reqType' => 'TSStimeSeries',
        ];

        $response = $this->client->request('GET', self::API_URL, ['query' => $query]);
        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException('Failed to retrieve prices (Error code ' . $response->getStatusCode() . ')');
        }

        $contentType = $response->getHeaders()['content-type'][0] ?? '';
        if (!str_starts_with($contentType, 'application/json')) {
            throw new \RuntimeException('Unexpected content type: ' . $contentType);
        }

        $data = json_decode($response->getContent(), true);
        if (!is_array($data) || !isset($data['data']) || !is_array($data['data'])) {
            throw new \RuntimeException('Unexpected Wiener Börse response format');
        }

        $prices = [];
        foreach ($data['data'] as $row) {
            if (!is_array($row)
                || !isset($row['DATETIME_LAST'], $row['FIRST'], $row['HIGH'], $row['LOW'], $row['LAST'], $row['TOTAL_VOLUME'])
                || !is_numeric($row['DATETIME_LAST'])
                || !is_numeric($row['FIRST'])
                || !is_numeric($row['HIGH'])
                || !is_numeric($row['LOW'])
                || !is_numeric($row['LAST'])
                || !is_numeric($row['TOTAL_VOLUME'])
            ) {
                throw new \RuntimeException('Found line with invalid format: ' . json_encode($row));
            }

            $date = new \DateTime('@' . intdiv((int) $row['DATETIME_LAST'], 1000));
            if ($date < $startdate || $date > $enddate) {
                continue;
            }

            $price = new AssetPrice();
            $price->setAsset($asset);
            $price->setDate($date);
            $price->setOHLC(
                (string) $row['FIRST'],
                (string) $row['HIGH'],
                (string) $row['LOW'],
                (string) $row['LAST']
            );
            $price->setVolume((int) $row['TOTAL_VOLUME']);

            $prices[] = $price;
        }

        return $prices;
    }
}

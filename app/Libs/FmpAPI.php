<?php
namespace App\Libs;

class FmpApi
{
    protected static $token;
    protected static $base_url = "https://fmpcloud.io/api/";
    protected static $version = "v3";
    protected static $url;

    public function __construct($token)
    {
        self::$token = $token;
        self::$url = self::$base_url . self::$version;
    }

    private function query_builder($path, $params = array())
    {
        $params['apikey'] = self::$token;
        $query = http_build_query($params, "", "&");
        try {
            $result = json_decode(file_get_contents(self::$url . $path . '?' . $query));
            return $result;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /*
        params: query
        return: symbol, name
    */
    public function get_search($query, $limit=20, $exchange='NASDAQ')
    {
        try {
            $params = array(
                'query' => $query,
                'limit' => $limit,
                'exchange' => $exchange
            );
            $result = $this->query_builder('/search', $params);
            return $result;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /*
        params: tickers=AAPC,FRUD
        return: symbol, name, open, price, changesPercentage, change, volume, timestamp, dayLow, dayHigh, yearHigh, yearLow, marketCap
    */
    public function get_quote($tickers)
    {
        try {
            $result = $this->query_builder('/quote/'.$tickers);
            return $result;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /*
        params: symbol, from, to
        return: symbol, open, close, high, low, change, changePercent, date, vwap, adjClose  // daily data
    */
    public function get_historical_prices($asset, $from=null, $to=null,$limit=null)
    {
        try {
            $params = array();
            if(isset($from))
                $params['from'] = $from;
            if(isset($to))
                $params['to'] = $to;
            if(isset($limit))
                $params['timeseries'] = $limit;

            $result = $this->query_builder('/historical-price-full/'.$asset, $params);
            return $result->historical;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /*
        params: symbol, duration=[1min, 5min, 15min, 30min, 1hour, 4hour]
        return: symbol, open, close, high, low, ema, volume, date
    */
    public function get_intraday_indicators($asset, $duration="15min")
    {
        try {
            $params = array(
                'period' => 10,
                'type' => 'ema'
            );

            $result = $this->query_builder('/technical_indicator/'.$duration.'/'.$asset, $params);
            $limit = 0;
            switch($duration)
            {
                case "15min":
                    $limit = 28; // one day
                    break;
                case "1hour":
                    $limit = 30; // 5 days
                    break;
            }
            return array_slice($result, 0, $limit);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /*
    return: ticker, changes, price, changesPercentage, companyName
    */
    public function get_top_gainers()
    {
        try {
            $result = $this->query_builder('/gainers');
            return $result;
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    public function get_top_Losers()
    {
        try {
            $result = $this->query_builder('/losers');
            return $result;
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    public function get_top_active()
    {
        try {
            $result = $this->query_builder('/actives');
            return $result;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /*
        params: stocks symbol
        return: symbol, companyName, exchange, industry, website, description, ceo, address, state, city, zip, cuontry, phone
    */
    public function get_company($asset)
    {
        try {
            $result = $this->query_builder('/profile/'.$asset);
            return $result[0];
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /*
        params: tickers=AAPC,FRUD, count
        return: publishedDate, title, site, image, text, url
    */
    public function get_news($tickers="", $count = 5)
    {
        $params = array(
            'tickers' => $tickers,
            'limit' => $count
        );

        try {
            $result = $this->query_builder('/stock_news', $params);
            return $result;
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}

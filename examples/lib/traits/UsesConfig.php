<?php

namespace Legalweb\CosmicCalendarClientExample\Lib\Traits;

use GetOpt\GetOpt;
use Yosymfony\Toml\Toml;
use Legalweb\CosmicCalendarClient\Config;

trait UsesConfig {

    /**
     * @param GetOpt $opt
     *
     * @return Config
     */
    protected function getCalendarConfig(GetOpt $opt) {
        $c = new Config();

        list($client, $secret, $endpoint) = $this->getConfigOptions($opt);

        $c->Name = "Example";
        $c->Client = $client;
        $c->Secret = $secret;
        $c->EndPoint = $endpoint;

        return $c;
    }

    /**
     * @param GetOpt $opt
     *
     * @return array
     */
    protected function getConfigOptions(GetOpt $opt) {
        $config = $opt->getOption('config');

        $client = "";
        $secret = "";
        $endpoint = "";

        if (is_null($config)) {
            $client = $opt->getOption('client');
            $secret = $opt->getOption('secret');
            $endpoint = $opt->getOption('endpoint');

            if (!$client || !$secret || !$endpoint) {
                throw new \InvalidArgumentException("client, secret & endpoint must be set if not providing config file");
            }
        } else {
            $c = Toml::parseFile($config);

            if (!$c['CLIENT'] || !$c['SECRET'] || !$c['ENDPOINT']) {
                throw new \InvalidArgumentException("client, secret & endpoint must be set if not providing config file");
            }

            $client = $c['CLIENT'];
            $secret = $c['SECRET'];
            $endpoint = $c['ENDPOINT'];
        }

        return [ $client, $secret, $endpoint ];
    }

}
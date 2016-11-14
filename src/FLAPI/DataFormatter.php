<?php
namespace FLAPI;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @class DataFormatter
 * @author Christoph "criztovyl" Schulz <ch.schulz@joinout.de>
 * @license GNU GPL v3.0
 * @package FLAPI
 */
class DataFormatter {

    /**
     * Formats the data as requested by ?format=:format
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param mixed $data the data to format
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function format(ResponseInterface $response, $data, $format) : ResponseInterface {

        switch($format){

        case "json":
            return $response->withJSON($data);

        case "yumyum":
            $response->write("YUM YUM DATA YUM YUM");
            return $response;

        default:
            throw new DataFormatException('Data format not available!');
        }
    }

}

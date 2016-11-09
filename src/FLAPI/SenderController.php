<?php
namespace FLAPI;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @class SenderController
 * @author Alexander Jank <himself@alexanderjank.de>
 * @license GNU GPL v3.0
 * @package FLAPI
 */
class SenderController {

	/**
	 * @var \Interop\Container\ContainerInterface
	 */
	protected $ci;

	/**
	 * @var \Illuminate\Database\Capsule\Manager
	 */
	protected $db;

	/**
	 * @var \Illuminate\Database\Query\Builder
	 */
	protected $senderTable;

	/**
	 * @var \Illuminate\Database\Query\Builder
	 */
	protected $sendungenTable;

	/**
	 * @var \Illuminate\Database\Query\Builder
	 */
	protected $downloadTable;

	/**
	 * @param \Interop\Container\ContainerInterface $ci
	 * @param \Illuminate\Database\Capsule\Manager $db
	 */
	public function __construct(\Interop\Container\ContainerInterface $ci, \Illuminate\Database\Capsule\Manager $db) {
		$this->ci = $ci;
		$this->senderTable = $db->table('sender');
		$this->sendungenTable = $db->table('sendungen');
		$this->downloadTable = $db->table('download');
		$this->db = $db;
	}

	/**
	 * Route-method for /sender
	 *
	 * @api {get} /sender?format=:format Request list of available stations
	 * @apiVersion 0.1.0
	 * @apiName GetAvailableStations
	 * @apiGroup Stations
	 *
	 * @apiParam {String} format The format you wish to receive the data in [json, xml]
	 *
	 * @apiSuccess {Object[]} sender The stations details
	 * @apiSuccess {String} sender.name  Full name of the station
	 * @apiSuccess {String} sender.abbr Abbreviation for the stations name
	 * @apiSuccess {String} sender.url URL to GET the full list of shows on that station
	 *
	 * @apiSuccessExample Success-Response:
	 *     HTTP/1.1 200 OK
	 *     [
	 *       {
	 *          "name": "ZDFneo",
	 *          "abbr": "zdfneo",
	 *          "url": "/sender/zdfneo"
	 *       },
	 *       {
	 *          "name": "arteDE",
	 *          "abbr": "arte",
	 *          "url": "/sender/arte"
	 *       }
	 *    ]
	 *
	 * @apiExample {curl} Example usage:
	 *     curl -i http://localhost:8080/sender/
	 *
	 * @param \Psr\Http\Message\ServerRequestInterface $request
	 * @param \Psr\Http\Message\ResponseInterface $response
	 * @param array $args
	 * @return \Psr\Http\Message\ResponseInterface
	 */
	public function getSenderliste(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
		$queryParams = $request->getQueryParams();
		if(!isset($queryParams['format'])) {
			$format = "json";
		} else {
			$format = $queryParams['format'];
		}
		$senderData = $this->senderTable->get(['name', 'abbr'])->toArray();
		foreach($senderData as &$sender) {
			$sender->url = $this->ci->get('router')->pathFor('senderFull', [
				'abbr' => $sender->abbr
			]);
		}
		if($format == "json") {
			return $response->withJSON($senderData, 200);
		} else {
			throw new \Exception('Data format not available!');
		}
	}
}

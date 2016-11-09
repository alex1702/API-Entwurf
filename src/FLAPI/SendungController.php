<?php
namespace FLAPI;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @class SendungController
 * @author Alexander Jank <himself@alexanderjank.de>
 * @license GNU GPL v3.0
 * @package FLAPI
 */
class SendungController {

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
	 * Route-method for /sender/{abbr}
	 *
	 * @api {get} /sender/:abbr?format=:format Request list of shows with this station
	 * @apiVersion 0.1.0
	 * @apiName GetAllStationShows
	 * @apiGroup Shows
	 *
	 * @apiParam {String} abbr The abbreviation of the station's name
	 * @apiParam {String} format The format you wish to receive the data in [json, xml]
	 *
	 * @apiSuccess {Object[]} sendung The stations details
	 * @apiSuccess {String} sendung.title  The shows title
	 * @apiSuccess {Number} sendung.date  Unix timestamp of the date and time when it was broadcast
	 * @apiSuccess {Number} sendung.length  The show's length in seconds
	 * @apiSuccess {Object[]} sendung.download  The download links for this show
	 * @apiSuccess {String} sendung.download.url  The URL for this download
	 * @apiSuccess {String} sendung.download.quality  The quality of this download link [SD, HQ, HD]
	 *
	 * @apiSuccessExample Success-Response:
	 *     HTTP/1.1 200 OK
	 *      [
	 *          {
	 *             "title":"Testsendung",
	 *             "date":1478622600,
	 *             "length":2700,
	 *             "download":[
	 *                 {
	 *                     "url":"http://mediathekview.de/testsendung-sd.mp4",
	 *                     "quality":"SD"
	 *                 },
	 *                 {
	 *                     "url":"http://mediathekview.de/testsendung-hq.mp4",
	 *                     "quality":"HQ"
	 *                 },
	 *                 {
	 *                     "url":"http://mediathekview.de/testsendung-hd.mp4",
	 *                     "quality":"HD"
	 *                 }
	 *            ]
	 *        }
	 *    ]
	 *
	 * @apiError DataFormatException The data-format given is not available!
	 *
	 * @apiErrorExample Error-Response:
	 *     HTTP/1.1 404 Not Found
	 *     {
	 *       "error": "DataFormatException"
	 *     }
	 *
	 * @apiError SenderNotFoundException The sender you were looking for was not found!
	 *
	 * @apiErrorExample Error-Response:
	 *     HTTP/1.1 404 Not Found
	 *     {
	 *       "error": "SenderNotFoundException"
	 *     }
	 *
	 * @apiExample {curl} Example usage:
	 *     curl -i http://localhost:8080/sender/zdfneo
	 *
	 * @param \Psr\Http\Message\ServerRequestInterface $request
	 * @param \Psr\Http\Message\ResponseInterface $response
	 * @param array $args
	 * @return \Psr\Http\Message\ResponseInterface
	 */
	public function getSenderAllData(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
		$queryParams = $request->getQueryParams();
		if(!isset($queryParams['format'])) {
			$format = "json";
		} else {
			$format = $queryParams['format'];
		}
		$senderId = $this->senderTable->where('abbr', '=', $args['abbr'])->get(['id'])->toArray();
		if(!isset($senderId[0]->id)) {
			throw new \Exception('Sender not found!');
		}
		$sendungen = $this->sendungenTable->where('sender', '=', $senderId[0]->id)->get(['title', 'date', 'length', 'id'])->toArray();
		foreach($sendungen as &$sendung) {
			$sendung->date = strtotime($sendung->date);
			$sendung->length = $this->timeToSec($sendung->length);
			$sendung->download = $this->downloadTable->where('sendung', '=', $sendung->id)->get(['url', 'quality']);
			unset($sendung->id);
		}
		if($format == "json") {
			return $response->withJSON($sendungen, 200);
		} else {
			throw new \Exception('Data format not available!');
		}
	}

	/**
	 * Route-method for /sender/{abbr}/{timeframe}
	 *
	 * @api {get} /sender/:abbr/:timeframe?format=:format Request specific list of shows on this station
	 * @apiVersion 0.1.0
	 * @apiName GetSpecialStationShows
	 * @apiGroup Shows
	 *
	 * @apiParam {String} abbr The abbreviation of the station's name
	 * @apiParam {String} timeframe The timeframe you want to get the data in.
	 * 		Formats:
	 *   		- Timestamp (start-timestamp)t(end-timestamp)
	 *     		- Days (number of days in past)d(number of days in the future)
	 *
	 * @apiParam {String} format The format you wish to receive the data in [json, xml]
	 *
	 * @apiSuccess {Object[]} sendung The stations details
	 * @apiSuccess {String} sendung.title  The shows title
	 * @apiSuccess {Number} sendung.date  Unix timestamp of the date and time when it was broadcast
	 * @apiSuccess {Number} sendung.length  The show's length in seconds
	 * @apiSuccess {Object[]} sendung.download  The download links for this show
	 * @apiSuccess {String} sendung.download.url  The URL for this download
	 * @apiSuccess {String} sendung.download.quality  The quality of this download link [SD, HQ, HD]
	 *
	 * @apiSuccessExample Success-Response:
	 *     HTTP/1.1 200 OK
	 *      [
	 *          {
	 *             "title":"Testsendung",
	 *             "date":1478622600,
	 *             "length":2700,
	 *             "download":[
	 *                 {
	 *                     "url":"http://mediathekview.de/testsendung-sd.mp4",
	 *                     "quality":"SD"
	 *                 },
	 *                 {
	 *                     "url":"http://mediathekview.de/testsendung-hq.mp4",
	 *                     "quality":"HQ"
	 *                 },
	 *                 {
	 *                     "url":"http://mediathekview.de/testsendung-hd.mp4",
	 *                     "quality":"HD"
	 *                 }
	 *            ]
	 *        }
	 *    ]
	 *
	 * @apiError DataFormatException The data-format given is not available!
	 *
	 * @apiErrorExample Error-Response:
	 *     HTTP/1.1 404 Not Found
	 *     {
	 *       "error": "DataFormatException"
	 *     }
	 *
	 * @apiError SenderNotFoundException The sender you were looking for was not found!
	 *
	 * @apiErrorExample Error-Response:
	 *     HTTP/1.1 404 Not Found
	 *     {
	 *       "error": "SenderNotFoundException"
	 *     }
	 *
	 * @apiExample {curl} Example usage:
	 *     curl -i http://localhost:8080/sender/zdfneo/30d0
	 *
	 * @apiExample {curl} Example usage:
	 *     curl -i http://localhost:8080/sender/zdfneo/1476981606t1478709606
	 *
	 * @param \Psr\Http\Message\ServerRequestInterface $request
	 * @param \Psr\Http\Message\ResponseInterface $response
	 * @param array $args
	 * @return \Psr\Http\Message\ResponseInterface
	 */
	public function getSenderSpecialData(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
		$queryParams = $request->getQueryParams();
		if(!isset($queryParams['format'])) {
			$format = "json";
		} else {
			$format = $queryParams['format'];
		}
		$senderId = $this->senderTable->where('abbr', '=', $args['abbr'])->get(['id'])->toArray();
		if(!isset($senderId[0]->id)) {
			throw new \Exception('Sender not found!');
		}
		list($start, $end) = $this->parseTimeframe($args['timeframe']);
		$sqlStart = date("Y-m-d H:i:s", $start);
		$sqlEnd = date("Y-m-d H:i:s", $end);
		$sendungen = $this->sendungenTable->where('sender', '=', $senderId[0]->id)->whereDate('date', '>=', $sqlStart)->whereDate('date', '<=', $sqlEnd)->get(['title', 'date', 'length', 'id'])->toArray();
		foreach($sendungen as &$sendung) {
			$sendung->date = strtotime($sendung->date);
			$sendung->length = $this->timeToSec($sendung->length);
			$sendung->download = $this->downloadTable->where('sendung', '=', $sendung->id)->get(['url', 'quality']);
			unset($sendung->id);
		}
		if($format == "json") {
			return $response->withJSON($sendungen, 200);
		} else {
			throw new \Exception('Data format not available!');
		}
	}

	/**
	 * Method for converting MySQL time to seconds
	 *
	 * @param string $time
	 * @return int
	 */
	private function timeToSec(string $time): int {
		$sec = 0;
		foreach (array_reverse(explode(':', $time)) as $k => $v) {
			$sec += pow(60, $k) * $v;
		}
		return $sec;
	}

	/**
	 * Mathod for parsing the user-given timeframe from the URL to a start and end
	 *
	 * @param string $timeframe
	 * @return array
	 */
	private function parseTimeframe(string $timeframe): array {
		$regex = '/(\d+)([td])(\d+)/';
		preg_match($regex, $timeframe, $matches);
		$method = $matches[2];
		$start = $matches[1];
		$end = $matches[3];
		switch($method) {
			case "t":
			break;

			case "d":
			$start = time() - ($start * 86400);
			$end = ($end * 86400) + time();
			break;

			default:
			throw new \Exception('Timeframe-type not supported!');
			break;
		}
		return [(int) $start, (int) $end];
	}
}

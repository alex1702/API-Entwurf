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
	 * Route-method for /sender/
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

	/**
	 * Route-method for /sender/{abbr}
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
		list($start, $end) = $this->parseTimeframe($args['timeframe']);
		//TODO: add more logic... @jankal
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

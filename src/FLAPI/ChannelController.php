<?php
namespace FLAPI;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @class ChannelController
 * @author Alexander Jank <himself@alexanderjank.de>
 * @license GNU GPL v3.0
 * @package FLAPI
 */
class ChannelController {

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
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param array $args
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getChannelList(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $senderData = $this->senderTable->get(['name', 'abbr'])->toArray();
        foreach($senderData as &$sender) {
            $sender->url = $this->ci->get('router')->pathFor('senderFull', [
                'abbr' => $sender->abbr
            ]);
        }

        return $this->ci->dataFormatter->format($response, $senderData, $request->getAttribute('format'));
    }

    /**
     * Route-method for /sender/{abbr}
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param array $args
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getChannelAllShows(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $senderId = $this->senderTable->where('abbr', '=', $args['abbr'])->get(['id'])->toArray();
        if(!isset($senderId[0]->id)) {
            throw new ChannelNotFoundException('Sender not found!');
        }
        $sendungen = $this->sendungenTable->where('sender', '=', $senderId[0]->id)->get(['title', 'date', 'length', 'id'])->toArray();
        foreach($sendungen as &$sendung) {
            $sendung->date = strtotime($sendung->date);
            $sendung->length = $this->timeToSec($sendung->length);
            $sendung->download = $this->downloadTable->where('sendung', '=', $sendung->id)->get(['url', 'quality']);
            unset($sendung->id);
        }

        return $this->ci->dataFormatter->format($response, $sendungen, $request->getAttribute('format'));
    }

    /**
     * Route-method for POST /sender
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param array $args
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function addChannel(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {

        $status = array();

        $body = (object) $request->getParsedBody();
        $abbr = $body->channel;
        $name = $body->name;

        if(($sender = $this->senderTable->where('abbr', $abbr)->first())) {

            $status["success"] = false;
            $status["message"] = "A Channel with abbr '$abbr' already exists.";
            $response = $response->withStatus(400);

            //$this->senderTable->where('id', $sender->id)->update(['name' => $name]);
        }
        else {

            $status["id"] = $this->senderTable->insertGetId([
                'abbr' => $abbr,
                'name' => $name
            ]);

            $status["success"]= true;
            $status["message"]= "Channel '$name'@'$abbr' successfully created.";


            $response = $response->withHeader('Location', $this->ci->get('router')->pathFor('senderFull', ['abbr' => $abbr]));
            $response = $response->withStatus(301);
        }

        return $this->ci->dataFormatter->format($response, $status, $request->getAttribute('format'));
    }

    /**
     * Route-method for PUT /sender/{abbr}
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param array $args
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function addReplaceChannel(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {

        $body = (object) $request->getParsedBody();
        $name = $body->name;

        $abbr = $args['abbr'];

        if(($sender = $this->senderTable->where('abbr', $abbr)->first())) {

            $this->senderTable->where('id', $sender->id)->update(['name' => $name]);

            $status["success"] = true;
            $status["replaced"] = true;
            $status["message"] = "Channel with abbr '{$sender->abbr}'@'$abbr' successfully replaced with '$name'@'$abbr'.";
        }
        else {

            $status["id"] = $this->senderTable->insertGetId([
                'abbr' => $abbr,
                'name' => $name
            ]);

            $status["success"]= true;
            $status["created"]= true;
            $status["message"]= "Channel '$name'@'$abbr' successfully created.";


        }

        return $this->ci->dataFormatter->format($response, $status, $request->getAttribute('format'));

    }

    /**
     * Route-method for /sender/{abbr}/{timeframe}
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param array $args
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getChannelNarrowdShows(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $senderId = $this->senderTable->where('abbr', '=', $args['abbr'])->get(['id'])->toArray();
        if(!isset($senderId[0]->id)) {
            throw new ChannelNotFoundException('Sender not found!');
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

        return $this->ci->dataFormatter->format($response, $sendungen, $request->getAttribute('format'));
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

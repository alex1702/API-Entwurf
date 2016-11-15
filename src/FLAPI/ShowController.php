<?php
namespace FLAPI;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @class ShowController
 * @author Alexander Jank <himself@alexanderjank.de>
 * @license GNU GPL v3.0
 * @package FLAPI
 */
class ShowController {

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
     * Route-method for POST /show
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param array $args
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function addShow(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {

        $body = (object) $request->getParsedBody();
        $status = array();
        $showDate = null;

        $abbr = $body->channel;
        $show = (object) $body->show;

        if(($timestamp = strtotime($show->date)) === FALSE){
            $status["success"] = false;
            $status["message"] = "Unparsable date '". $show->date . "'";
        }
        else {

            if(($sender = $this->senderTable->where('abbr', $abbr)->first())){

                $id = $this->sendungenTable->insertGetId([
                    'title' => $show->title,
                    'date' => date("Y-m-d H:i:s", $timestamp),
                    'length' => $show->length,
                    'sender' => $sender->id
                ]);

                $status["success"] = true;
                $status["id"] = $id;
            }
            else {
                $status["success"] = false;
                $status["message"] = "No such channel '$abbr'!";

                $response = $response->withStatus(404);
            }
        }

        return $this->ci->dataFormatter($response, $status, $request->getAttribute('format'));

    }

}

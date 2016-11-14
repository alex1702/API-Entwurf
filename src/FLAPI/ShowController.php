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
}

<?php
// Routes

/**
 * @api {get} /sender?format=:format Request list of available stations
 * @apiVersion 0.1.0
 * @apiName GetChannelList
 * @apiGroup Stations
 *
 * @apiParam {String} format The format you wish to receive the data in [json, xml]
 *
 * @apiSuccess {Object[]} station The stations details
 * @apiSuccess {String} station.name  Full name of the station
 * @apiSuccess {String} station.abbr Abbreviation for the stations name
 * @apiSuccess {String} station.url URL to GET the full list of shows on that station
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
 */
$app->get('/sender', \FLAPI\ChannelController::class . ':getChannelList');

/**
 * @api {get} /sender/:abbr?format=:format Request list of shows with this station
 * @apiVersion 0.1.0
 * @apiName GetChannelAllShows
 * @apiGroup Shows
 *
 * @apiParam {String} abbr The abbreviation of the station's name
 * @apiParam {String} format The format you wish to receive the data in [json, xml]
 *
 * @apiSuccess {Object[]} show The stations details
 * @apiSuccess {String} show.title  The shows title
 * @apiSuccess {Number} show.date  Unix timestamp of the date and time when it was broadcast
 * @apiSuccess {Number} show.length  The show's length in seconds
 * @apiSuccess {Object[]} show.download  The download links for this show
 * @apiSuccess {String} show.download.url  The URL for this download
 * @apiSuccess {String} show.download.quality  The quality of this download link [SD, HQ, HD]
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
 * @apiError ChannelNotFoundException The sender you were looking for was not found!
 *
 * @apiErrorExample Error-Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "error": "ChannelNotFoundException"
 *     }
 *
 * @apiExample {curl} Example usage:
 *     curl -i http://localhost:8080/sender/zdfneo
 */
$app->get('/sender/{abbr}', \FLAPI\ChannelController::class . ':getChannelAllShows')->setName('senderFull');

/**
 * @api {get} /sender/:abbr/:timeframe?format=:format Request specific list of shows on this station
 * @apiVersion 0.1.0
 * @apiName GetChannelNarrowdShows
 * @apiGroup Shows
 *
 * @apiParam {String} abbr The abbreviation of the station's name
 * @apiParam {String} timeframe The timeframe you want to get the data in.
 *         Formats:
 *           - Timestamp (start-timestamp)t(end-timestamp)
 *             - Days (number of days in past)d(number of days in the future)
 *
 * @apiParam {String} format The format you wish to receive the data in [json, xml]
 *
 * @apiSuccess {Object[]} show The stations details
 * @apiSuccess {String} show.title  The shows title
 * @apiSuccess {Number} show.date  Unix timestamp of the date and time when it was broadcast
 * @apiSuccess {Number} show.length  The show's length in seconds
 * @apiSuccess {Object[]} show.download  The download links for this show
 * @apiSuccess {String} show.download.url  The URL for this download
 * @apiSuccess {String} show.download.quality  The quality of this download link [SD, HQ, HD]
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
 * @apiError ChannelNotFoundException The sender you were looking for was not found!
 *
 * @apiErrorExample Error-Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "error": "ChannelNotFoundException"
 *     }
 *
 * @apiExample {curl} Example usage:
 *     curl -i http://localhost:8080/sender/zdfneo/30d0
 *
 * @apiExample {curl} Example usage:
 *     curl -i http://localhost:8080/sender/zdfneo/1476981606t1478709606
 *
 */
$app->get('/sender/{abbr}/{timeframe}', \FLAPI\ChannelController::class . ':getChannelNarrowdShows');

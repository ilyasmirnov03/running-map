<?php
namespace App\Websocket;

use Exception;
use SplObjectStorage;
use Ratchet\ConnectionInterface;
use Psr\Http\Message\RequestInterface;
use Ratchet\MessageComponentInterface;
use Symfony\Component\Validator\Constraints\Unique;

class MessageHandler implements MessageComponentInterface
{
    protected $connections, $runs, $container;

    public function __construct()
    {
        $this->runs = array();
        $this->connections = array();
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->connections[] = $conn;
        $conn->send(json_encode('..:: Connected to WS ::..'));
        echo "New connection \n";
    }

    public function onMessage(ConnectionInterface $from, $m)
    {
        $data = json_decode(trim($m));
        switch($data->function ?? "") {
            case "connect": $this->connectToRun($data, $from); break;
            case "update": $this->updateCoords($data, $from); break;
        }
    }

    public function connectToRun(object $data, ConnectionInterface $from) {
        if(!isset($data->run_id)) {
            $from->close();
        }
        if(!isset($this->runs[$data->run_id])) {
            $this->runs[$data->run_id] = array(
                "runners" => array(),
                "watchers" => array()
            );
            $from->send(json_encode("..:: Created run $data->run_id ::.."));
            echo "New run $data->run_id \n";
        }
        if(isset($data->runner_id)) {
            // ! CONNECT AS RUNNER
            foreach($this->connections as $key => $conn){
                if($conn === $from){
                    $this->runs[$data->run_id]["runners"][$data->runner_id] = $from;
                    unset($this->connections[$key]);
                    $from->send(json_encode("..:: Joined run $data->run_id as runner $data->runner_id ::.."));
                    echo "New runner joined $data->run_id as $data->runner_id \n";
                    break;
                }
            }
        } else {
            // ! CONNECT AS WATCHER
            foreach($this->connections as $key => $conn){
                if($conn === $from){
                    $uniqueId = uniqid() . uniqid();
                    $this->runs[$data->run_id]["watchers"][$uniqueId] = $from;
                    unset($this->connections[$key]);
                    $from->send(json_encode("..:: Joined run $data->run_id as watcher $uniqueId ::.."));
                    echo "New watcher joined $data->run_id as $uniqueId \n";
                    break;
                }
            }
        }
    }

    public function updateCoords(object $data, ConnectionInterface $from) {
        if (!isset($data->run_id) || !isset($data->runner_id)) {
            $from->close();
        }
        if($this->runs[$data->run_id]["runners"][$data->runner_id] == $from) {
            $from->send(json_encode("..:: Updating coords.. ::.."));
            // echo "Updating coords \n";
            foreach ($this->runs[$data->run_id]["watchers"] as $key => $conn) {
                // ! THIS IS FOR TESTING ONLY 
                $conn->send(json_encode("..:: Updating coords.. ::.."));
                if(is_array($data->coords)) {
                    echo "Updating all coords \n";
                    foreach($data->coords as $key => $value) {
                        $conn->send(json_encode(array(
                            "run_id" => $data->run_id,
                            "runner" => $value->runner->id,
                            "coords" => $value->coords
                        )));
                    }
                } else {
                    $conn->send(json_encode(array(
                        "run_id" => $data->run_id,
                        "runner" => $data->runner_id,
                        "coords" => $data->coords
                    )));
                }
            }
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        foreach($this->runs as $id => $container){
            foreach($container as $key => $conn_element)
            if($conn === $conn_element){
                unset($this->runs[$id][$key]);
                break;
            }
        }
    }

    public function onError(ConnectionInterface $conn, Exception $e)
    {
        $conn->send(json_encode("Error: " . $e->getMessage()));
        $conn->close();
    }
}

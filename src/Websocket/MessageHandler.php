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
    protected $connections, $run, $container;

    public function __construct()
    {
        $this->run = array();
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
            case "coords": $this->updateCoords($data, $from); break;
        }
    }

    public function updateCoords($data, $from) {
        if(isset($data->run_id) && isset($data->runner_id)) {
            if(isset($this->run[$data->run_id]) && $this->run[$data->run_id][$data->runner_id] == $from) {
                foreach($this->run[$data->run_id] as $key => $conn){
                    if($conn !== $from){
                        $conn->send(json_encode(array(
                            "run_id" => $data->run_id,
                            "runner" => $data->runner_id,
                            "coords" => $data->coords ?? []
                        )));
                    }
                }
            }
        }
    }

    public function connectToRun(object $data, ConnectionInterface $conn) {
        if(isset($data->run_id)) {
            if(isset($this->run[$data->run_id])) {
                if(!isset($data->runner_id)) {
                    // ! WATCHER/ADMIN
                    $data->runner_id = uniqid() . uniqid();
                }
                foreach($this->connections as $key => $exist){
                    if($conn === $exist){
                        $this->run[$data->run_id][$data->runner_id] = $conn;
                        unset($this->connections[$key]);
                        break;
                    }
                }
                $conn->send(json_encode('..:: Connected in run '.$data->run_id.' as '.$data->runner_id.' ::..'));
                echo "New connection $data->run_id \n";
            } else {
                // ! CREATING RUN FROM RUN ID
                if(isset($this->run[$data->run_id])) {
                    $conn->close();
                }

                $this->run[$data->run_id] = array();
                // ! IS ADMIN
                $data->runner_id = uniqid() . uniqid();

                foreach($this->connections as $key => $exist){
                    if($conn === $exist){
                        $this->run[$data->run_id][$data->runner_id] = $conn;
                        unset($this->connections[$key]);
                        break;
                    }
                }

                $conn->send(json_encode('..:: Connected in run '.$data->run_id.' as '.$data->runner_id.' and creating run ::..'));
                echo "New insert connection $data->run_id \n";
            }
        } else {
            $conn->close();
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        foreach($this->run as $id => $container){
            foreach($container as $key => $conn_element)
            if($conn === $conn_element){
                unset($this->run[$id][$key]);
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
?>
<?php
    namespace App\Command;
     
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;
    use App\Websocket\MessageHandler;
    use Ratchet\Server\IoServer;
    use Ratchet\Http\HttpServer;
    use Ratchet\WebSocket\WsServer;
     
    class WebsocketServerCommand extends Command
    {
        protected static $defaultName = "run:ws";
        protected function execute(InputInterface $input, OutputInterface $output)
        {
            $port = 3001;
            $output->writeln("Starting server on port " . $port);
            $server = IoServer::factory(
                new HttpServer(
                    new WsServer(
                        new MessageHandler()
                    )
                ),
                $port
            );
            $server->run();
            return 0;
        }
    }
?>
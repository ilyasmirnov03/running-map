(function(){
    window.addEventListener('load', async () => {
        await RunnerInterface.init();
        await WS.init();
    });

    const RunnerInterface = {
        init: async () => {
            console.log(RunId);
            RunnerInterface.id = UserId;
            RunnerInterface.run_id = RunId;

            RunnerInterface.interval = setInterval(async () => {
                await RunnerInterface.update();
            }, 5000)

        },
        update: async () => {
            const coords = await RunnerInterface.fetch_run(
                Math.floor(new Date().getTime() / 1000.0)
            );
            const UserCoords = coords.find(user => user.runner.id === RunnerInterface.id).coords;
            console.log(coords);

            // ! HERE WE POST ALL COORDS AS IF WE WERE ALL RUNNER IN TIME JUST TO TEST (REPLACE WITH UserCoords)
            WS.send({ run_id: RunnerInterface.run_id, runner_id: RunnerInterface.id, coords: coords, function: "update" });
        },
        fetch_run: async (timestamp) => {
            const f = await fetch(`/coords/${RunnerInterface.run_id}/${timestamp}`);
            const c = await f.json();
            return c;
        }
    }

    const WS = {
        init: async (port = 3001) => {
            WS.server = new WebSocket(`ws://localhost:${port}`);
            WS.server.addEventListener("open", WS.onOpen);
            WS.server.addEventListener("message", WS.onMessage);
        },
        onOpen: async (e) => {
            WS.send({ run_id: RunnerInterface.run_id, runner_id: RunnerInterface.id, function: "connect" }); // RUNNER
        },
        send: (message) => {
            WS.server.send(JSON.stringify(message));
        },
        onMessage: async (e) => {
            console.log(e.data);
        },
    }
})();
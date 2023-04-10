(function(){
    window.addEventListener('load', async () => {
        await RunnerInterface.init();
        await WS.init();
    });

    const RunnerInterface = {
        RUN_MODE: 2,
        DEMO_MODE: 1,
        init: async () => {

            RunnerInterface.mode = RunnerInterface.DEMO_MODE; // ! DEFINE DEMO MODE

            if(RunDate > Math.floor(new Date().getTime() / 1000.0)) {
                return alert("La course n'a pas commencé!");
            }

            RunnerInterface.id = UserId; // TWIG IMPORT
            RunnerInterface.run_id = RunId; // TWIG IMPORT

            if(RunnerInterface.mode === RunnerInterface.RUN_MODE) {
                RunnerInterface.getCoords();
            } else {
                RunnerInterface.interval = setInterval(async () => {
                    await RunnerInterface.update();
                }, 5000); // DEMO MODE
            }
        },
        getCoords: () => {
            if ('geolocation' in navigator) {
                document.addEventListener('click', function () {
                  navigator.geolocation.getCurrentPosition(function (location) {
                    RunnerInterface.update(location);
                  });
                  RunnerInterface.position = navigator.geolocation.watchPosition(RunnerInterface.update);
                });
              } else {
                console.log('Geolocation API not supported.');
              }
        },
        update: async (data) => {
            if(RunnerInterface.mode === RunnerInterface.RUN_MODE) {
                console.log(data);
                const Coords = {
                    latitude: data.coords.latitude,
                    longitude: data.coords.longitude,
                    date: Math.floor(data.timestamp / 1000.0),
                }
                console.log("REAL COORDS");
                console.log(Coords);
                WS.send({ run_id: RunnerInterface.run_id, runner_id: RunnerInterface.id, coords: Coords, function: "update" });
            } else {
                const coords = await RunnerInterface.fetch_run(
                    Math.floor(new Date().getTime() / 1000.0)
                );
                const UserCoords = coords.find(user => user.runner.id === RunnerInterface.id).coords;
                console.log(coords);
                // ! HERE WE POST ALL COORDS AS IF WE WERE ALL RUNNER IN TIME JUST TO TEST (REPLACE WITH UserCoords)
                WS.send({ run_id: RunnerInterface.run_id, runner_id: RunnerInterface.id, coords: coords, function: "update" });
            }
        },
        fetch_run: async (timestamp) => {
            const f = await fetch(`/coords/${RunnerInterface.run_id}/${timestamp}`);
            const c = await f.json();
            return c;
        }
    }

    const WS = {
        init: async (port = 3001) => {
            WS.addr = RunnerInterface.mode === RunnerInterface.RUN_MODE ? "runningmaps.alwaysdata.net" : "localhost";
            WS.server = new WebSocket(`ws://${WS.addr}:${port}`);
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
window.addEventListener('DOMContentLoaded', async () => {
    await App.init();
});

const Run = {
    init: async (...run) => {
        Object.entries(run[0]).forEach((k) => {
            Run[k[0]] = k[1];
        });
        await WS.init(Run.id);
        await App.loadKMLTrack(Run.map);
        // TODO: ADMIN setView Global
        App.map.fitBounds(App.bounds);
    }
}

const WS = {
    init: async (run_id, port = 3001) => {
        WS.id = run_id;
        WS.server = new WebSocket(`ws://localhost:${port}`);
        WS.server.addEventListener("open", WS.onOpen);
        WS.server.addEventListener("message", WS.onMessage);
    },
    onOpen: async (e) => {
        // WS.send({ run_id: WS.id, is_admin: true, function: "connect" });
        WS.send({ run_id: WS.id, runner_id: 0, function: "connect" });
        // WS.send({ run_id: WS.id, runner_id: 0, function: "coords", coords: [1, 5] });
        // ! IF IS RUNNER THIS THING UNDER SHOULD BE DISABLED
        const f = await fetch(`/coords/${WS.id}/${new Date().getTime()}`);
        const c = await f.json();
        console.log(c);
        await App.loadMarkers(c);
    },
    // ! IF IS RUNNER THIS FUNC SHOULD BE DISABLED
    onMessage: async (e) => {
        console.log(JSON.parse(e.data));
        if(e.data.coords) {
            App.updateMarkers([e.data]);
        }
    },
    send: (message) => {
        console.log(message);
        WS.server.send(JSON.stringify(message));
    }
}

const App = {
    MAX_ZOOM: 18, 
    MIN_ZOOM: 17,
    MARKER_BOX_SIZE: 38,
    TRACK_STYLE: { opacity: 1, weight: 13, color: "#ff4f64" },
    MARKER_MANAGER: null,
    IS_ADMIN: false,
    init: async () => {
        App.map = L.map('map', { 
            preferCanvas: true, 
            minZoom: App.MIN_ZOOM,
            maxZoom: App.MAX_ZOOM
        });
        App.tileLayer = new L.TileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png');
        App.map.addLayer(App.tileLayer);
    },
    setView: async (coords = [App.bounds._northEast.lat, App.bounds._northEast.lng]) => {
        App.map.setView(coords, App.MAX_ZOOM);
    },
    loadKMLTrack: async (path = "default.kml") => {
        await fetch(`/assets/map/${path}`).then(res => res.text()).then(kmltext => {
            parser = new DOMParser();
            kml = parser.parseFromString(kmltext, "text/xml");

            const track = new L.KML(kml);
            track.setStyle(App.TRACK_STYLE);
            App.map.addLayer(track);

            App.bounds = track.getBounds();
        });
    },
    loadMarkers: async (runners) => {
        App.MARKER_MANAGER = new App.UserMarkerManager(runners);
    },
    updateMarkers: async (runners) => {
        runners.forEach(runner => {
            App.UserMarkerManager.MarkerCollection[runner.runner].update(runner);
        });
    },
    UserMarker: class {
        constructor (runner) {
            this.marker = L.icon({
                iconUrl: runner.picture ?? "/assets/users/default.png",
                iconSize: [App.MARKER_BOX_SIZE, App.MARKER_BOX_SIZE],
                iconAnchor: [App.MARKER_BOX_SIZE/2, App.MARKER_BOX_SIZE],
                popupAnchor: [0, -App.MARKER_BOX_SIZE - 8],
                // shadowUrl: "/assets/users/shadow.png",
                // shadowSize:   [App.MARKER_BOX_SIZE * 2 - 12, App.MARKER_BOX_SIZE * 2 - 12],
                // shadowAnchor: [App.MARKER_BOX_SIZE - 6, App.MARKER_BOX_SIZE + 4],
                className: "runner-marker"
            });
            this.addMarker(runner.coords);
            this.setPopup(runner);
        }
        addMarker(coords) {
            this.markerObject = L.marker([coords.latitude, coords.longitude], { icon: this.marker }).addTo(App.map);
            // console.log(this.markerObject);
        }
        getMarker() {
            return this.marker;
        }
        setPos(coords) {
            // if(this.pos) {
            //     this.speed = this.pos // TODO CALC WITH "coords"
            // }
            this.pos = coords;
            this.markerObject?.setLatLng([coords.latitude, coords.longitude]);
        }
        setPopup(runner) {
            // TODO: USER SPEED
            this.markerObject.bindPopup(`Coureur : ${runner.login ?? "Franck"} <br> Vitesse coureur : ${this.speed ?? 0}km/h`, { width: 120 });
        }
        update (runner) {
            this.setPos(runner.coords);
            this.setPopup(runner);
        } 
    },
    UserMarkerManager: class {
        static MarkerCollection = {};
        constructor (runners) {
            runners.forEach(runner => {
                const Marker = new App.UserMarker(runner);
                App.UserMarkerManager.MarkerCollection[runner.runner] = Marker;
            });
        }
    }
}
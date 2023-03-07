window.addEventListener('DOMContentLoaded', async () => {
    await WS.init();
    await App.init();
});

const WS = {
    init: async (port = 3001) => {
        const socket = new WebSocket(`ws://localhost:${port}`);
        socket.addEventListener("open", WS.onOpen);
        socket.addEventListener("message", WS.onMessage);
    },
    onOpen: (e) => {
        console.log("WS Opened");
    },
    onMessage: (e) => {
        console.log(e.data);
    },
    send: (message) => {
        socket.send(JSON.stringify(message));
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
        await App.loadKMLTrack();
        // TODO: ADMIN setView Global
        await App.setView([App.bounds._northEast.lat, App.bounds._northEast.lng]);
        await App.loadMarkers([]); // ! PLUG RUNNERS FROM TABLE
    },
    setView: async (coords = [45.649674, 0.1405531]) => {
        App.map.setView(coords, App.MAX_ZOOM);
    },
    loadKMLTrack: async (path = "/assets/map/default.kml") => {
        await fetch(path).then(res => res.text()).then(kmltext => {
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
            App.UserMarkerManager.MarkerCollection[runner.id].update(runner);
        });
    },
    UserMarker: class {
        constructor (runner) {
            this.marker = L.icon({
                iconUrl: runner.picture ?? "/assets/users/default.png",
                iconSize: [App.MARKER_BOX_SIZE, App.MARKER_BOX_SIZE],
                iconAnchor: [App.MARKER_BOX_SIZE/2, App.MARKER_BOX_SIZE],
                popupAnchor: [0, -App.MARKER_BOX_SIZE - 8],
                shadowUrl: "/assets/users/shadow.png",
                shadowSize:   [App.MARKER_BOX_SIZE * 2 - 12, App.MARKER_BOX_SIZE * 2 - 12],
                shadowAnchor: [App.MARKER_BOX_SIZE - 6, App.MARKER_BOX_SIZE + 4],
                className: "runner-marker"
            });
            this.addMarker(runner.coords);
            this.setPopup(runner);
        }
        addMarker(coords) {
            this.markerObject = L.marker(coords, { icon: this.marker }).addTo(App.map);
        }
        getMarker() {
            return this.marker;
        }
        setPos(coords) {
            if(this.pos) {
                this.speed = this.pos // TODO CALC WITH "coords"
            }
            this.pos = coords;
            this.marker?.setLatLng(coords);
        }
        setPopup(runner) {
            // TODO: USER SPEED
            this.markerObject.bindPopup(`Coureur : ${runner.login} <br> Vitesse coureur : ${runner.speed}km/h`, { width: 120 });
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
                App.UserMarkerManager.MarkerCollection[runner.id] = Marker;
            });
        }
    }
}
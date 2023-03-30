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
        WS.send({ run_id: WS.id, is_admin: true, function: "connect" });
        // WS.send({ run_id: WS.id, runner_id: 0, function: "connect" });
        // WS.send({ run_id: WS.id, runner_id: 0, function: "coords", coords: [1, 5] });
        const f = await fetch(`/coords/${WS.id}/${(new Date().getTime() / 1000).toFixed(0)}`);
        const c = await f.json();
        console.log(c);
        await App.loadMarkers(c);
        // ! IF IS RUNNER THIS THING UNDER SHOULD BE DISABLED
        let F = setInterval(async() => {
            const f = await fetch(`/coords/${WS.id}/${(new Date().getTime() / 1000).toFixed(0)}`);
            const c = await f.json();
            console.log(c);
            await App.updateMarkers(c);
        }, 5000)
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

const CalcDistance = function (lat1, lon1, lat2, lon2) {
    function toRad(Value) 
    {
        return Value * Math.PI / 180;
    }
    var R = 6371; // km
    var dLat = toRad(lat2-lat1);
    var dLon = toRad(lon2-lon1);
    var lat1 = toRad(lat1);
    var lat2 = toRad(lat2);

    var a = Math.sin(dLat/2) * Math.sin(dLat/2) +
        Math.sin(dLon/2) * Math.sin(dLon/2) * Math.cos(lat1) * Math.cos(lat2); 
    var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)); 
    var d = R * c;
    return d;
}

const App = {
    MAX_ZOOM: 18, 
    MIN_ZOOM: 5,
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
            App.UserMarkerManager.MarkerCollection[runner.runner.id].update(runner);
        });
    },
    UserMarker: class {
        constructor (runner) {
            this.marker = L.icon({
                iconUrl: `/assets/users/${runner.runner.picture}` ?? "/assets/users/default.png",
                iconSize: [App.MARKER_BOX_SIZE, App.MARKER_BOX_SIZE],
                iconAnchor: [App.MARKER_BOX_SIZE/2, App.MARKER_BOX_SIZE],
                popupAnchor: [0, -App.MARKER_BOX_SIZE - 8],
                className: "runner-marker"
            });
            this.init();
        }
        init() {
            this.object = L.marker([this.coords.latitude, this.coords.longitude], { icon: this.icon }).addTo(App.map).bindPopup(`Coureur : ${this.runner.login ?? "Mr.Cheater"} <br> Vitesse coureur : ${this.speed ?? 0} km/h`, { width: 120 });
        }
        getMarker() {
            return this.marker;
        }
        setCoords(coords) {
            if(this.pos && !this.speed) {
                let distance = Utility.distance_fp(this.pos.latitude, this.pos.longitude, coords.latitude, coords.longitude);
                let timespend = coords.date - Math.floor(new Date().getTime() / 1000.0);
                this.speed = Math.abs((distance / (timespend / 60 / 60)).toFixed(2));
            }
            this.pos = coords;
            this.object.setLatLng([coords.latitude, coords.longitude]);
        }
        setPopup() {
            this.object._popup.setContent(`Coureur : ${this.runner.login ?? "Mr.Cheater"} <br> Vitesse coureur : ${this.speed ?? 0} km/h`)
        }
        update (coords, speed = null) {
            console.log(coords);
            if(this.pos?.latitude == coords.latitude && this.pos?.longitude == coords.longitude) return;
            this.speed = speed;
            this.setCoords(coords);
            this.setPopup();
        } 
    }
    
    const Utility = {
        fetch: async (url, body = [], method = 'GET') => {
            const context = await fetch(url, { method: method, headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' }, body: JSON.stringify(body) });
            const response = await context.json(); return response;
        },
        to_rad: (v) => {
            return v * Math.PI / 180;
        },
        distance_fp: (lat1, lon1, lat2, lon2) => {
            var R = 6371; // ? earth km
            var dLat = Utility.to_rad(lat2-lat1);
            var dLon = Utility.to_rad(lon2-lon1);
            var lat1 = Utility.to_rad(lat1);
            var lat2 = Utility.to_rad(lat2);
            var a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                Math.sin(dLon/2) * Math.sin(dLon/2) * Math.cos(lat1) * Math.cos(lat2); 
            var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)); 
            var d = R * c;
            return d;
        },
        fetch_kml: async (path) => {
            const c = await fetch(`/assets/map/${path}`);
            const r = await c.text();
            parser = new DOMParser();
            kml = parser.parseFromString(r, "text/xml");
            return kml;
        },
        fetch_run: async (timestamp) => {
            const f = await fetch(`/coords/${App.run.id}/${timestamp}`);
            const c = await f.json();
            return c;
        }
    }
}
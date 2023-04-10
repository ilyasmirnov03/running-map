(function(){
    window.addEventListener('DOMContentLoaded', async () => {
        await App.init(Run); // TWIG IMPORT
    });
    
    const App = {
        MODE: "PROD", // ! PROD FOR PROD
        MAX_ZOOM: 18, 
        MIN_ZOOM: 5,
        MARKER_BOX_SIZE: 38,
        TRACK_STYLE: { opacity: 1, weight: 13, color: "#ff4f64" },
        MARKERS: [],
        init: async (run) => {
            App.run = run;
            App.map = L.map('map', { 
                preferCanvas: true, 
                minZoom: App.MIN_ZOOM,
                maxZoom: App.MAX_ZOOM
            });
            if(App.run.finished_at) {
                await RunHistory.init();
            } else {
                await WS.init();
            }
            await App.loadKMLTrack(run.map);
            App.map.fitBounds(App.bounds);
            App.tileLayer = new L.TileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png');
            App.map.addLayer(App.tileLayer);
        },
        setView: async (coords) => {
            App.map.setView(coords, App.MAX_ZOOM);
        },
        loadKMLTrack: async (path = "default.kml") => {
            const KML = await Utility.fetch_kml(path);
            const Track = new L.KML(KML);
            Track.setStyle(App.TRACK_STYLE);
            App.map.addLayer(Track);
            App.bounds = Track.getBounds();
        },
        initMarkers: async (runners) => {
            // ! In o you have runner info and coords info
            runners.forEach(o => {
                App.MARKERS[o.runner.id] = new RunnerMarker(o);
            });
        }
    }
    
    const RunHistory = {
        init: async () => {
            RunHistory.rangeInput = document.querySelector('.time-choice');
            if(!RunHistory.rangeInput) return;
            RunHistory.rangeInput.value = 0;
            RunHistory.timeout = null;
            RunHistory.rangeInput.addEventListener('input', RunHistory.updateTimestamp);
            App.initMarkers(await Utility.fetch_run(Math.floor(new Date().getTime() / 1000.0)));
        },
        updateTimestamp: (evt) => {
            if(!RunHistory.rangeInput) return;
            clearTimeout(RunHistory.timeout);
            // ? fetch only half a second after selecting
            RunHistory.timeout = setTimeout(async () => {
                const Runners = await Utility.fetch_run(RunHistory.rangeInput.value);
                Runners.forEach(o => {
                    App.MARKERS[o.runner.id].update(o.coords, 20);
                });
            }, 500);
        }
    }
    
    const WS = {
        init: async (port = 3001) => {
            WS.id = App.run.id;
            console.log("Connected to WS");
            WS.addr = App.MODE === "PROD" ? "runningmaps.alwaysdata.net" : "localhost";

            // ! FIXING WS IN PROD
            // ! BUILT INTERVAL FOR API ROUTE
            // ! FOR DEMO ONLY

            if(App.MODE === "PROD") {
                App.initMarkers(await Utility.fetch_run(Math.floor(new Date().getTime() / 1000.0)));
                WS.server = setInterval(async () => {
                    const Data = await Utility.fetch_run(Math.floor(new Date().getTime() / 1000.0));
                    Data.forEach(e => {
                        App.MARKERS[e.runner.id].update(e.coords);
                    });
                }, 5000) // ? 5 SECONDES INTERVAL
                return;
            }
            
            WS.server = new WebSocket(`ws://${WS.addr}:${port}`);
            WS.server.addEventListener("open", WS.onOpen);
            WS.server.addEventListener("message", WS.onMessage);
        },
        onOpen: async (e) => {
            App.initMarkers(await Utility.fetch_run(Math.floor(new Date().getTime() / 1000.0)));
            WS.send({ run_id: WS.id, function: "connect" }); // WATCHER
        },
        onMessage: async (e) => {
            const data = JSON.parse(e.data);
            console.log(data);
            if(data.coords) {
                App.MARKERS[data.runner].update(data.coords);
            }
        },
        send: (message) => {
            WS.server.send(JSON.stringify(message));
        }
    }
    
    const RunnerMarker = class {
        constructor (o) {
            this.runner = o.runner;
            this.coords = o.coords;
            this.icon = L.icon({
                iconUrl: `/assets/users/${this.runner.picture}` ?? "/assets/users/default.png",
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
            this.object._popup.setContent(`Coureur : ${this.runner.login ?? "Mr.Cheater"} <br> Vitesse coureur : ${isNaN(this.speed) || this.speed == null ? 0 : this.speed} km/h`)
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
})()
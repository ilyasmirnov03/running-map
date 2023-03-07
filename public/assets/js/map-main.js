window.addEventListener('DOMContentLoaded', async () => {
    await App.init();
});

const App = {
    MAX_ZOOM: 18, 
    MIN_ZOOM: 17,
    MARKER_BOX_SIZE: 38,
    TRACK_STYLE: { opacity: 1, weight: 13, color: "#5639b8" },
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
        App.userId = 0; // ! PLUG USER FROM TABLE + COORDONATES
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
        users.forEach(user => {
            App.UserMarkerManager.MarkerCollection[user.id].update(user);
        });
    },
    UserMarker: class {
        constructor (user) {
            this.marker = L.icon({
                iconUrl: user.picture ?? "/assets/users/default.png",
                iconSize: [App.MARKER_BOX_SIZE, App.MARKER_BOX_SIZE],
                iconAnchor: [App.MARKER_BOX_SIZE/2, App.MARKER_BOX_SIZE],
                popupAnchor: [0, -App.MARKER_BOX_SIZE - 8],
                shadowUrl: "/assets/users/shadow.png",
                shadowSize:   [App.MARKER_BOX_SIZE * 2 - 12, App.MARKER_BOX_SIZE * 2 - 12],
                shadowAnchor: [App.MARKER_BOX_SIZE - 6, App.MARKER_BOX_SIZE + 4],
                className: App.userId === user.id ? "user-marker" : "default-marker"
            });
            this.addMarker(user.coords);
            this.setPopup(user);
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
        setPopup(user) {
            // TODO: USER SPEED
            this.markerObject.bindPopup(`Coureur : ${user.login} <br> Vitesse coureur : ${user.speed}km/h`, { width: 120 });
        }
        update (user) {
            this.setPos(user.coords);
            this.setPopup(user);
        } 
    },
    UserMarkerManager: class {
        static MarkerCollection = {};
        constructor (users) {
            users.forEach(user => {
                const Marker = new App.UserMarker(user);
                App.UserMarkerManager.MarkerCollection[user.id] = Marker;
            });
        }
    }
}
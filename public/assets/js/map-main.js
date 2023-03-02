window.addEventListener('load', () => {
    var map = L.map('map', { preferCanvas: true }), osm = new L.TileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png');
    map.addLayer(osm);

    fetch("/assets/map/default.kml")
        .then(res => res.text())
        .then(kmltext => {
            parser = new DOMParser();
            kml = parser.parseFromString(kmltext, "text/xml");

            const track = new L.KML(kml);
            console.log(track);
            track.setStyle({ opacity: 1, weight: 13, color: "#5639b8" });
            map.addLayer(track);

            const bounds = track.getBounds();
            map.fitBounds(bounds);
        });

    var UserMarker = L.icon({
        iconUrl: "/assets/users/default.png",
        iconSize: [38, 38],
        iconAnchor: [19, 38],
        popupAnchor: [0, -30]
    });
    L.marker([45.64772587855568, 0.13676991313307407], { icon: UserMarker }).addTo(map).bindPopup("Coureur : Fred <br> Vitesse coureur : 12km/h", { width: 120 });
});
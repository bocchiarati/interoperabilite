async function getJSON(url) {
    const response = await fetch(url);
    if (!response.ok) throw new Error(`Erreur chargement ${url}`);
    return response.json();
}

async function init() {
    // --------------------
    // IP
    // --------------------
    const geo = await getJSON("https://ipapi.co/json/");
    let lat, lon;
    const ville = geo.city;

    if (ville !== "Nancy") {
        lat = 48.68298;
        lon = 6.16095;
    } else {
        lat = geo.latitude;
        lon = geo.longitude;
    }


    // --------------------
    // METEO
    // --------------------
    const response = await fetch(
        `https://www.infoclimat.fr/public-api/gfs/xml?_ll=${lat},${lon}&_auth=ARsDFFIsBCZRfFtsD3lSe1Q8ADUPeVRzBHgFZgtuAH1UMQNgUTNcPlU5VClSfVZkUn8AYVxmVW0Eb1I2WylSLgFgA25SNwRuUT1bPw83UnlUeAB9DzFUcwR4BWMLYwBhVCkDb1EzXCBVOFQoUmNWZlJnAH9cfFVsBGRSPVs1UjEBZwNkUjIEYVE6WyYPIFJjVGUAZg9mVD4EbwVhCzMAMFQzA2JRMlw5VThUKFJiVmtSZQBpXGtVbwRlUjVbKVIuARsDFFIsBCZRfFtsD3lSe1QyAD4PZA%3D%3D&_c=19f3aa7d766b6ba91191c8be71dd1ab2`
    );
    const xmlText = await response.text();
    const parser = new DOMParser();
    const xmlDoc = parser.parseFromString(xmlText, "application/xml");

    const eches = Array.from(xmlDoc.getElementsByTagName("echeance"));
    const todayEch = new Date();

    const todayEches = Array.from(eches).filter(e => {
        const tsStr = e.getAttribute("timestamp");
        const tsDate = new Date(tsStr);
        return tsDate.getUTCFullYear() === todayEch.getUTCFullYear() &&
            tsDate.getUTCMonth() === todayEch.getUTCMonth() &&
            tsDate.getUTCDate() === todayEch.getUTCDate();
    });

    const bestEcheance = todayEches.reduce((prev, curr) => {
        const prevDiff = Math.abs(new Date(prev.getAttribute("timestamp")) - todayEch);
        const currDiff = Math.abs(new Date(curr.getAttribute("timestamp")) - todayEch);
        return currDiff < prevDiff ? curr : prev;
    });

    const tempC = Math.round(bestEcheance.getElementsByTagName("level")[1]?.textContent - 273.15);
    const vent = bestEcheance.getElementsByTagName("vent_moyen")[0]?.textContent;
    const pluie = bestEcheance.getElementsByTagName("pluie")[0]?.textContent;


    // --------------------
    // POLLUTION
    // --------------------
    const data = await getJSON(
        "https://services3.arcgis.com/Is0UwT37raQYl9Jj/arcgis/rest/services/ind_grandest/FeatureServer/0/query?where=lib_zone%3D'Nancy'&outFields=*&returnGeometry=true&f=pjson"
    );

    if (!data.features || data.features.length === 0) {
        throw new Error("Aucune donnée pour Nancy");
    }

    const today = new Date();
    today.setUTCHours(0, 0, 0, 0);
    const start = today.getTime();
    const end = start + 86400000;

    const todayFeature = data.features.find(f =>
        f.attributes.date_ech >= start && f.attributes.date_ech < end
    );

    const indicePollution = todayFeature.attributes.code_qual;
    const libQualite = todayFeature.attributes.lib_qual;

    // --------------------
    // VELOS
    // --------------------
    const gbfs = await getJSON("https://api.cyclocity.fr/contracts/nancy/gbfs/gbfs.json");
    const feeds = gbfs.data.fr.feeds;

    const stationInfoUrl = feeds.find(f => f.name === "station_information").url;
    const stationStatusUrl = feeds.find(f => f.name === "station_status").url;

    const stationInfo = await getJSON(stationInfoUrl);
    const stationStatus = await getJSON(stationStatusUrl);

    const stations = stationInfo.data.stations.map(st => {
        const status = stationStatus.data.stations.find(s => s.station_id === st.station_id);
        return {
            name: st.name,
            lat: st.lat,
            lon: st.lon,
            bikes: status ? status.num_bikes_available : 0,
            docks: status ? status.num_docks_available : 0
        };
    });


    // --------------------
    // DECISION
    // --------------------
    let decision = "🚲 Conseillé";
    let raisons = [];

    if (tempC < 3) raisons.push("froid");
    if (tempC > 30) raisons.push("trop chaud");
    if (vent > 10) raisons.push("vent fort");
    if (pluie > 0) raisons.push("pluie");
    if (indicePollution > 2) raisons.push("pollution élevée");
    if (stations.every(s => s.bikes === 0)) raisons.push("aucun vélo disponible");

    if (raisons.length > 0) decision = "🚫 Déconseillé";

    document.getElementById("decision-section").innerHTML = `
        <h2 data-decision="${decision === "🚲 Conseillé" ? "conseillé" : "déconseillé"}">
            ${decision}
        </h2>
        ${decision === "🚫 Déconseillé" ? `<p>Raison(s) : ${raisons.join(", ")}</p>` : ""}
    `;

    document.getElementById("meteo-section").innerHTML = `
        <h2>Météo</h2>
        <p>Température : ${tempC}°C</p>
        <p>Vent : ${vent} km/h</p>
        <p>Pluie : ${pluie} mm</p>
    `;

    document.getElementById("pollution-section").innerHTML = `
        <h2>Qualité de l'air</h2>
        <p>${libQualite}</p>
    `;

    const ul = document.getElementById("stations");
    ul.innerHTML = ``;
    stations.sort((a, b) => b.bikes - a.bikes)
        .forEach(s => {
            const li = document.createElement("li");
            li.innerHTML = `<strong>${s.name}</strong>
            <p>Nombre de vélos disponibles : ${s.bikes}</p>
            <p>Nombre de places disponibles : ${s.docks}</p>`;
            ul.appendChild(li);
        });

}

init().catch(err => console.error(err));

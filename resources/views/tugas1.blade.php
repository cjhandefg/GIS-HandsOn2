<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Peta Kampus di Bali</title>
  <!-- Leaflet.js CDN -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <!-- Google Maps API -->
  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDAzTbmVWNqyhYGcud_zDsdCxxJ9wNZjfw"></script>
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .map-container {
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
      border-radius: 0.75rem;
      overflow: hidden;
    }

    /* Map hover effect */
    .map-container:hover {
      transform: scale(1.02);
      transition: transform 0.3s ease-in-out;
    }
  </style>
</head>

<body class="bg-gradient-to-br from-blue-50 to-blue-100 min-h-screen flex flex-col justify-center items-center">
  <div class="container mx-auto px-4 py-8">
    <div class="text-center mb-8">
      <h1 class="text-4xl font-bold text-blue-800 mb-4 drop-shadow-md hover:text-blue-600 transition duration-300">
        📍 Peta Lokasi Kampus di Bali
      </h1>
      <p class="text-blue-600 max-w-2xl mx-auto text-lg">
        Jelajahi lokasi kampus terkemuka di Pulau Bali. Klik marker untuk informasi lebih detail dan zoom otomatis.
      </p>
    </div>

    <div class="flex flex-wrap justify-center gap-6">
      <div class="w-full md:w-1/2 lg:w-5/12 map-container">
        <div class="bg-white p-2 rounded-t-lg">
          <h2 class="text-xl font-semibold text-blue-700 text-center">Leaflet Map</h2>
        </div>
        <div id="leaflet-map" class="h-[500px] w-full"></div>
      </div>

      <div class="w-full md:w-1/2 lg:w-5/12 map-container">
        <div class="bg-white p-2 rounded-t-lg">
          <h2 class="text-xl font-semibold text-blue-700 text-center">Google Maps</h2>
        </div>
        <div id="google-map" class="h-[500px] w-full"></div>
      </div>
    </div>

    <!-- Tombol Reset Map -->
    <div class="flex justify-center mt-6">
      <button onclick="resetLeafletMap()" class="px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-500 transition duration-300 mr-2">Reset Leaflet Map</button>
      <button onclick="resetGoogleMap()" class="px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-500 transition duration-300">Reset Google Map</button>
    </div>

    <div class="mt-8 text-center">
      <div class="bg-white shadow-md rounded-lg inline-block p-4">
        <h3 class="text-xl font-semibold text-blue-800 mb-2">Informasi Tambahan</h3>
        <p class="text-blue-600">
          Data lokasi kampus diperbarui terakhir pada Desember 2024
        </p>
      </div>
    </div>
  </div>

  <footer class="mt-12 text-center bg-blue-100 py-4">
    <p class="text-blue-800 text-sm">
      2024 Peta Kampus Bali.
    </p>
  </footer>

  <script>
    const locations = [{
        name: "Rektorat Universitas Udayana",
        lat: -8.7984047,
        lng: 115.1698715,
        description: "Kantor Pusat Universitas Udayana"
      },
      {
        name: "Politeknik Negeri Bali",
        lat: -8.798613179371184,
        lng: 115.16252991521414,
        description: "Kampus Politeknik Negeri Bali"
      },
      {
        name: "Universitas Pendidikan Ganesha",
        lat: -8.705056765169523,
        lng: 115.21804592747553,
        description: "Kampus UNDIKSHA Denpasar"
      },
      {
        name: "Universitas Mahasaraswati",
        lat: -8.652956902043861,
        lng: 115.224581978212,
        description: "Kampus Universitas Mahasaraswati Denpasar"
      },
      {
        name: "Universitas Ngurah Rai",
        lat: -8.619390156336198,
        lng: 115.23567505973286,
        description: "Kampus Universitas Ngurah Rai Denpasar"
      },
      {
        name: "Institut Seni Indonesia Denpasar",
        lat: -8.653436404295592,
        lng: 115.23261786777724,
        description: "Kampus Institut Seni Indonesia Denpasar"
      },
      {
        name: "Universitas Warmadewa",
        lat: -8.659046708248933,
        lng: 115.24268342729353,
        description: "Kampus Universitas Warmadewa Denpasar"
      },
      {
        name: "Universitas Hindu Indonesia",
        lat: -8.633477897013153,
        lng: 115.24363895243643,
        description: "Kampus Universitas Hindu Indonesia"
      },
      {
        name: "Universitas Dhyana Pura",
        lat: -8.628751595291133,
        lng: 115.17725584078762,
        description: "Kampus Universitas Dhyana Pura"
      },
      {
        name: "Universitas Bali Dwipa",
        lat: -8.675175275730783,
        lng: 115.20940181010727,
        description: "Kampus Universitas Bali Dwipa Denpasar"
      },
      {
        name: "Universitas Pendidikan Nasional",
        lat: -8.696162052907772,
        lng: 115.22637451010749,
        description: "Kampus Universitas Pendidikan Nasional (Undiknas) Denpasar"
      },
      {
        name: "STMIK Primakara",
        lat: -8.689423083369235,
        lng: 115.23786488496414,
        description: "Kampus STMIK Primakara Denpasar"
      }
    ];

    const leafletMap = L.map('leaflet-map').setView([-8.7, 115.2], 10);
    L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
      attribution: 'Tiles &copy; Esri &mdash; Source: Esri'
    }).addTo(leafletMap);

    locations.forEach(location => {
      const marker = L.marker([location.lat, location.lng]).addTo(leafletMap);
      marker.bindPopup(`<b>${location.name}</b><br>${location.description}`);
      marker.on('click', function() {
        leafletMap.setView([location.lat, location.lng], 15);
      });
    });

    const googleMapDiv = document.getElementById('google-map');
    const googleMap = new google.maps.Map(googleMapDiv, {
      center: {
        lat: -8.7,
        lng: 115.2
      },
      zoom: 10
    });

    locations.forEach(location => {
      const marker = new google.maps.Marker({
        position: {
          lat: location.lat,
          lng: location.lng
        },
        map: googleMap,
        title: location.name
      });

      const infoWindow = new google.maps.InfoWindow({
        content: `<b>${location.name}</b><br>${location.description}`
      });

      marker.addListener("click", () => {
        googleMap.setZoom(15);
        googleMap.setCenter(marker.getPosition());
        infoWindow.open(googleMap, marker);
      });
    });

    // Fungsi reset Leaflet Map
    function resetLeafletMap() {
      leafletMap.setView([-8.7, 115.2], 10);
    }

    // Fungsi reset Google Map
    function resetGoogleMap() {
      googleMap.setZoom(10);
      googleMap.setCenter({
        lat: -8.7,
        lng: 115.2
      });
    }
  </script>
</body>

</html>

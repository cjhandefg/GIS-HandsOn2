<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Peta Kampus di Bali</title>

  <!-- AdminLTE CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.1.0/css/adminlte.min.css">
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
  <!-- Leaflet.js CDN -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <!-- Google Maps API -->
  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC4lKVb0eLSNyhEO-C_8JoHhAvba6aZc3U&libraries=drawing"></script>  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Leaflet Draw -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
  <style>
    .map-container {
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
      border-radius: 0.75rem;
      overflow: hidden;
    }

    .map-container:hover {
      transform: scale(1.02);
      transition: transform 0.3s ease-in-out;
    }

    body {
      background-color: #f4f6f9;
    }
    .coordinate-cell {
        transition: background-color 0.3s ease;
        cursor: pointer;
    }
    
    .coordinate-cell:hover {
        background-color: #f8f9fa;
    }
  </style>
</head>

<body>
<div class="container-fluid">
    <!-- Navbar -->
    <nav class="navbar navbar-expand navbar-white navbar-light mb-4 shadow-sm">
        <div class="container">
            <a class="navbar-brand font-weight-bold text-primary" href="#">Peta Kampus Bali</a>
        </div>
    </nav>

    <div class="container">
        <div class="text-center mb-4">
            <h1 class="display-4 font-weight-bold text-primary mb-4 drop-shadow-md hover:text-blue-600 transition duration-300">
                📍 Peta Lokasi Kampus di Bali
            </h1>
            <p class="text-blue-600 max-w-2xl mx-auto text-lg">
                Jelajahi lokasi kampus terkemuka di Pulau Bali. Klik marker untuk informasi lebih detail dan zoom otomatis.
            </p>
        </div>

        <!-- Maps and Forms Row -->
        <div class="row">
            <!-- Left side - Maps -->
            <div class="col-md-8">
                <div class="row">
                    <!-- Leaflet Map -->
                    <div class="col-md-6">
                        <div class="map-container">
                            <div class="bg-white p-2 rounded-t-lg">
                                <h2 class="text-xl font-semibold text-blue-700 text-center">Leaflet Map</h2>
                            </div>
                            <div id="leaflet-map" class="h-[600px] w-full"></div>
                            <div class="text-center mt-3 mb-4">
                                <button onclick="resetLeafletMap()" class="btn btn-secondary">Reset Leaflet Map</button>
                            </div>
                        </div>
                    </div>

                    <!-- Google Map -->
                    <div class="col-md-6">
                        <div class="map-container">
                            <div class="bg-white p-2 rounded-t-lg">
                                <h2 class="text-xl font-semibold text-blue-700 text-center">Google Maps</h2>
                            </div>
                            <div id="google-map" class="h-[600px] w-full"></div>
                            <div class="text-center mt-3 mb-4">
                                <button onclick="resetGoogleMap()" class="btn btn-secondary">Reset Google Map</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right side - Forms -->
            <div class="col-md-4">
                <!-- Add Marker Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">Tambahkan Marker</h3>
                    </div>
                    <div class="card-body">
                        <form id="markerForm" class="space-y-4">
                            @csrf
                            <div class="form-group">
                                <input type="text" id="markerName" name="name" placeholder="Nama Lokasi" required class="form-control" />
                            </div>
                            <div class="form-group">
                                <input type="text" id="markerLat" name="lat" placeholder="Latitude" required class="form-control" />
                            </div>
                            <div class="form-group">
                                <input type="text" id="markerLng" name="lng" placeholder="Longitude" required class="form-control" />
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Tambah Marker</button>
                        </form>
                    </div>
                </div>

                <!-- Add Polygon Form -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Tambahkan Poligon</h3>
                    </div>
                    <div class="card-body">
                        <form id="polygonForm" class="space-y-4">
                            <div class="form-group">
                                <textarea id="polygonCoords" placeholder="Koordinat Poligon (JSON)" required class="form-control" rows="4"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Tambah Poligon</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Tables Row -->
        <div class="row mt-4">
            <!-- Markers Table -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Daftar Marker</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped" id="markersTable">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Latitude</th>
                                    <th>Longitude</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="markersTableBody">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Polygons Table -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Daftar Poligon</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped" id="polygonsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Koordinat</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="polygonsTableBody">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Info -->
        <div class="mt-4 text-center">
            <div class="card">
                <div class="card-body">
                    <h3 class="text-xl font-semibold text-primary mb-2">Informasi Tambahan</h3>
                    <p class="text-blue-600">Data lokasi kampus diperbarui terakhir pada Desember 2024</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer mt-4 py-3 bg-light text-center">
        <div class="container">
            <span class="text-muted">2024 &copy; Peta Kampus Bali</span>
        </div>
    </footer>
</div>

<!-- Edit Marker Modal -->
<div class="modal fade" id="editMarkerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Marker</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="editMarkerForm">
                    @csrf
                    <input type="hidden" id="editMarkerId">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" id="editMarkerName" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Latitude</label>
                        <input type="text" id="editMarkerLat" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Longitude</label>
                        <input type="text" id="editMarkerLng" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Polygon Modal -->
<div class="modal fade" id="editPolygonModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Polygon</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="editPolygonForm">
                    @csrf
                    <input type="hidden" id="editPolygonId">
                    <div class="form-group">
                        <label>Coordinates (JSON)</label>
                        <textarea id="editPolygonCoords" class="form-control" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

  </div>
  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
  <!--  AdminLTE -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.1.0/js/adminlte.min.js"></script>

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
  <script src="{{ asset('js/maps.js') }}"></script>
</body>

</html>
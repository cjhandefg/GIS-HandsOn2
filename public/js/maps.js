window.showFullCoordinates = function(cell, coordinates) {
  if (cell.getAttribute('data-expanded') === 'true') {
      // If expanded, show truncated version
      cell.textContent = coordinates.substring(0, 30) + '...';
      cell.setAttribute('data-expanded', 'false');
      cell.style.whiteSpace = 'nowrap';
  } else {
      // If truncated, show full version
      cell.textContent = coordinates;
      cell.setAttribute('data-expanded', 'true');
      cell.style.whiteSpace = 'normal';
  }
};

document.addEventListener('DOMContentLoaded', function() {
  let leafletDrawControl;
  let isDrawingPolygon = false;
  let currentPolygonPoints = [];

  // Function to populate markers table
  function populateMarkersTable(markers) {
    const tableBody = document.getElementById('markersTableBody');
    tableBody.innerHTML = '';

    markers.forEach(marker => {
      const row = `
        <tr data-id="${marker.id}">
          <td>${marker.name}</td>
          <td>${marker.latitude}</td>
          <td>${marker.longitude}</td>
          <td>
            <button onclick="editMarker(${marker.id}, '${marker.name}', ${marker.latitude}, ${marker.longitude})" 
              class="btn btn-primary btn-sm mr-2">Edit</button>
            <button onclick="deleteMarker(${marker.id})" class="btn btn-danger btn-sm">Hapus</button>
          </td>
        </tr>
      `;
      tableBody.innerHTML += row;
    });
  }

  function populatePolygonsTable(polygons) {
    const tableBody = document.getElementById('polygonsTableBody');
    tableBody.innerHTML = '';

    polygons.forEach(polygon => {
      const truncatedCoords = polygon.coordinates.substring(0, 30) + '...';
      const escapedCoords = polygon.coordinates.replace(/'/g, "\\'");
      const row = `
        <tr data-id="${polygon.id}">
          <td>${polygon.id}</td>
          <td class="coordinate-cell cursor-pointer hover:bg-gray-100" 
              onclick="showFullCoordinates(this, '${escapedCoords}')"
              title="Click to show/hide full coordinates"
              style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
              ${truncatedCoords}
          </td>
          <td>
            <button onclick="editPolygon(${polygon.id}, '${escapedCoords}')" 
              class="btn btn-primary btn-sm mr-2">Edit</button>
            <button onclick="deletePolygon(${polygon.id})" 
              class="btn btn-danger btn-sm">Hapus</button>
          </td>
        </tr>
      `;
      tableBody.innerHTML += row;
    });
  }

  // Initialize Leaflet draw plugin
  function initLeafletDraw() {
    const drawControl = new L.Control.Draw({
      draw: {
        marker: true,
        polygon: {
          allowIntersection: false,
          drawError: {
            color: '#e1e100',
            timeout: 1000
          },
          shapeOptions: {
            color: '#0000FF',
            fillOpacity: 0.3
          }
        },
        circle: false,
        rectangle: false,
        circlemarker: false,
        polyline: false
      }
    });
    
    leafletMap.addControl(drawControl);

    // Handle draw created event
    leafletMap.on('draw:created', function(e) {
      const type = e.layerType;
      const layer = e.layer;

      if (type === 'marker') {
        const latLng = layer.getLatLng();
        const name = prompt('Enter marker name:');
        
        if (name) {
          fetch("/api/markers", {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
              name: name,
              latitude: latLng.lat,
              longitude: latLng.lng
            }),
          })
          .then(res => res.json())
          .then(() => {
            location.reload();
          });
        }
      } else if (type === 'polygon') {
        const coordinates = layer.getLatLngs()[0].map(latLng => [latLng.lat, latLng.lng]);
        
        fetch("/api/polygons", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify({
            coordinates: coordinates
          }),
        })
        .then(res => res.json())
        .then(() => {
          location.reload();
        });
      }
    });
  }

  // Load Leaflet Draw
  const leafletDrawCSS = document.createElement('link');
  leafletDrawCSS.rel = 'stylesheet';
  leafletDrawCSS.href = 'https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css';
  document.head.appendChild(leafletDrawCSS);

  const leafletDrawJS = document.createElement('script');
  leafletDrawJS.src = 'https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js';
  document.head.appendChild(leafletDrawJS);

  leafletDrawJS.onload = function() {
    initLeafletDraw();
  };

// Function to show full coordinates
function showFullCoordinates(cell, coordinates) {
  if (cell.getAttribute('data-expanded') === 'true') {
    cell.textContent = coordinates.substring(0, 30) + '...';
    cell.setAttribute('data-expanded', 'false');
    cell.style.whiteSpace = 'nowrap';
  } else {
    cell.textContent = coordinates;
    cell.setAttribute('data-expanded', 'true');
    cell.style.whiteSpace = 'normal';
  }
}
  // Fetch and display existing markers
  fetch("/api/markers")
    .then(response => response.json())
    .then(markers => {
      // Populate markers table
      populateMarkersTable(markers);

      markers.forEach(marker => {
        // Add marker to Leaflet Map
        const leafletMarker = L.marker([marker.latitude, marker.longitude]).addTo(leafletMap);
        leafletMarker.bindPopup(`<b>${marker.name}</b>`);
        leafletMarker.on('click', function() {
          leafletMap.setView([marker.latitude, marker.longitude], 15);
        });

        // Add marker to Google Maps
        const googleMarker = new google.maps.Marker({
          position: { 
            lat: parseFloat(marker.latitude), 
            lng: parseFloat(marker.longitude) 
          },
          map: googleMap,
          title: marker.name
        });

        const infoWindow = new google.maps.InfoWindow({
          content: `<b>${marker.name}</b>`
        });

        googleMarker.addListener("click", () => {
          googleMap.setZoom(15);
          googleMap.setCenter(googleMarker.getPosition());
          infoWindow.open(googleMap, googleMarker);
        });
      });
    });

  // Fetch and display existing polygons
  fetch("/api/polygons")
    .then(response => response.json())
    .then(polygons => {
      // Populate polygons table
      populatePolygonsTable(polygons);

      polygons.forEach(polygon => {
        try {
          const coords = JSON.parse(polygon.coordinates);

          // Add polygon to Leaflet
          const leafletPolygon = L.polygon(coords, { color: 'blue' }).addTo(leafletMap);
          leafletPolygon.bindPopup('Existing Polygon');

          // Add polygon to Google Maps
          const googlePolygon = new google.maps.Polygon({
            paths: coords.map(coord => ({ 
              lat: coord[0], 
              lng: coord[1] 
            })),
            strokeColor: '#0000FF',
            strokeOpacity: 0.8,
            strokeWeight: 2,
            fillColor: '#0000FF',
            fillOpacity: 0.35
          });
          googlePolygon.setMap(googleMap);
        } catch (error) {
          console.error('Error parsing polygon coordinates:', error);
        }
      });      
    });

  // Add marker form event listener
  document.getElementById("markerForm").addEventListener("submit", function (e) {
    e.preventDefault();
    const name = document.getElementById("markerName").value;
    const lat = parseFloat(document.getElementById("markerLat").value);
    const lng = parseFloat(document.getElementById("markerLng").value);

    fetch("/api/markers", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({ name, latitude: lat, longitude: lng }),
    })
    .then((res) => res.json())
    .then((data) => {
      // Add marker to Leaflet Map
      const leafletMarker = L.marker([lat, lng]).addTo(leafletMap);
      leafletMarker.bindPopup(`<b>${name}</b>`);
      leafletMarker.on('click', function() {
        leafletMap.setView([lat, lng], 15);
      });

      // Add marker to Google Maps
      const googleMarker = new google.maps.Marker({
        position: { lat, lng },
        map: googleMap,
        title: name
      });

      const infoWindow = new google.maps.InfoWindow({
        content: `<b>${name}</b>`
      });

      googleMarker.addListener("click", () => {
        googleMap.setZoom(15);
        googleMap.setCenter(googleMarker.getPosition());
        infoWindow.open(googleMap, googleMarker);
      });

      // Refresh markers table
      fetch("/api/markers")
        .then(response => response.json())
        .then(markers => {
          populateMarkersTable(markers);
        });

      alert("Marker ditambahkan!");
      // Reset form
      document.getElementById("markerName").value = '';
      document.getElementById("markerLat").value = '';
      document.getElementById("markerLng").value = '';
    });
  });

// For adding polygons (in the polygonForm submit handler)
document.getElementById("polygonForm").addEventListener("submit", function (e) {
  e.preventDefault();
  let coords = JSON.parse(document.getElementById("polygonCoords").value);

  // Convert coordinates if they're in {lat, lng} format
  if (coords[0].hasOwnProperty('lat')) {
      coords = coords.map(coord => [coord.lat, coord.lng]);
  }

  fetch("/api/polygons", {
      method: "POST",
      headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({ coordinates: coords }),
  })
  .then((res) => res.json())
  .then((data) => {
      // For Leaflet, we can use the array format directly
      const leafletPolygon = L.polygon(coords, { color: 'blue' }).addTo(leafletMap);
      leafletPolygon.bindPopup('New Polygon');

      // For Google Maps, convert back to {lat, lng} format
      const googlePolygon = new google.maps.Polygon({
          paths: coords.map(coord => ({
              lat: coord[0],
              lng: coord[1]
          })),
          strokeColor: '#0000FF',
          strokeOpacity: 0.8,
          strokeWeight: 2,
          fillColor: '#0000FF',
          fillOpacity: 0.35
      });
      googlePolygon.setMap(googleMap);

      // Refresh polygons table
      fetch("/api/polygons")
          .then(response => response.json())
          .then(polygons => {
              populatePolygonsTable(polygons);
          });

      alert("Polygon ditambahkan!");
      document.getElementById("polygonCoords").value = '';
  });
});

// For displaying existing polygons (in the fetch("/api/polygons") handler)
fetch("/api/polygons")
  .then(response => response.json())
  .then(polygons => {
      populatePolygonsTable(polygons);

      polygons.forEach(polygon => {
          try {
              let coords = JSON.parse(polygon.coordinates);
              
              // Convert coordinates if they're in {lat, lng} format
              if (coords[0].hasOwnProperty('lat')) {
                  coords = coords.map(coord => [coord.lat, coord.lng]);
              }

              // Add polygon to Leaflet
              const leafletPolygon = L.polygon(coords, { color: 'blue' }).addTo(leafletMap);
              leafletPolygon.bindPopup('Existing Polygon');

              // Add polygon to Google Maps
              const googlePolygon = new google.maps.Polygon({
                  paths: coords.map(coord => ({
                      lat: coord[0],
                      lng: coord[1]
                  })),
                  strokeColor: '#0000FF',
                  strokeOpacity: 0.8,
                  strokeWeight: 2,
                  fillColor: '#0000FF',
                  fillOpacity: 0.35
              });
              googlePolygon.setMap(googleMap);
          } catch (error) {
              console.error('Error parsing polygon coordinates:', error);
          }
      });
  });

  // Edit marker form event listener
  document.getElementById('editMarkerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const id = document.getElementById('editMarkerId').value;
    const name = document.getElementById('editMarkerName').value;
    const lat = parseFloat(document.getElementById('editMarkerLat').value);
    const lng = parseFloat(document.getElementById('editMarkerLng').value);

    fetch(`/api/markers/${id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({ name, latitude: lat, longitude: lng })
    })
    .then(response => response.json())
    .then(data => {
      $('#editMarkerModal').modal('hide');
      // Refresh markers
      fetch("/api/markers")
        .then(response => response.json())
        .then(markers => {
          populateMarkersTable(markers);
          // Refresh the maps (you might want to clear and re-add all markers)
          location.reload(); // Simple solution - reload the page
        });
      alert('Marker updated successfully!');
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Failed to update marker');
    });
  });

  // Edit polygon form event listener
  document.getElementById('editPolygonForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const id = document.getElementById('editPolygonId').value;
    const coordinates = document.getElementById('editPolygonCoords').value;

    fetch(`/api/polygons/${id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({ coordinates: JSON.parse(coordinates) })
    })
    .then(response => response.json())
    .then(data => {
      $('#editPolygonModal').modal('hide');
      // Refresh polygons
      fetch("/api/polygons")
        .then(response => response.json())
        .then(polygons => {
          populatePolygonsTable(polygons);
          // Refresh the maps (you might want to clear and re-add all polygons)
          location.reload(); // Simple solution - reload the page
        });
      alert('Polygon updated successfully!');
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Failed to update polygon');
    });
  });
});

// Function to delete marker
function deleteMarker(id) {
  fetch(`/api/markers/${id}`, {
    method: "DELETE",
    headers: {
      "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      "Accept": "application/json",
      "Content-Type": "application/json"
    }
  })
  .then(response => {
    // Check if the response is OK (status in the range 200-299)
    if (!response.ok) {
      // If not OK, try to parse the error response
      return response.json().then(errData => {
        throw new Error(errData.message || 'Failed to delete marker');
      });
    }
    return response.json();
  })
  .then(data => {
    // Remove marker from table
    const row = document.querySelector(`#markersTableBody tr[data-id="${id}"]`);
    if (row) {
      row.remove();
    }

    alert("Marker dihapus!");
    location.reload(); // Refresh the page to update the maps
  })
  .catch(error => {
    console.error('Error:', error);
    alert(error.message || "Gagal menghapus marker");
  });
}

// Function to delete polygon
function deletePolygon(id) {
  fetch(`/api/polygons/${id}`, {
    method: "DELETE",
    headers: {
      "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      "Accept": "application/json",
      "Content-Type": "application/json"
    }
  })
  .then(response => {
    // Check if the response is OK (status in the range 200-299)
    if (!response.ok) {
      // If not OK, try to parse the error response
      return response.json().then(errData => {
        throw new Error(errData.message || 'Failed to delete polygon');
      });
    }
    return response.json();
  })
  .then(data => {
    // Remove polygon from table
    const row = document.querySelector(`#polygonsTableBody tr[data-id="${id}"]`);
    if (row) {
      row.remove();
    }

    alert("Polygon dihapus!");
    location.reload(); // Refresh the page to update the maps
  })
  .catch(error => {
    console.error('Error:', error);
    alert(error.message || "Gagal menghapus polygon");
  });
}

// Function to edit marker
function editMarker(id, name, lat, lng) {
  document.getElementById('editMarkerId').value = id;
  document.getElementById('editMarkerName').value = name;
  document.getElementById('editMarkerLat').value = lat;
  document.getElementById('editMarkerLng').value = lng;
  $('#editMarkerModal').modal('show');
}

// Function to edit polygon
function editPolygon(id, coordinates) {
  document.getElementById('editPolygonId').value = id;
  document.getElementById('editPolygonCoords').value = coordinates;
  $('#editPolygonModal').modal('show');
}
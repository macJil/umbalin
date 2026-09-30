let map, previewCircle, previewMarker;
let layers = [];
let editingId = null;

const ITOGON_CENTER = { lat: 16.3667, lng: 120.6833 };
const ITOGON_BOUNDS = L.latLngBounds([
  [16.20, 120.52],
  [16.52, 120.76]
]);

const TYPE_COLORS = {
  earthquake: '#ef4444',
  typhoon: '#8b5cf6',
  landslide: '#8B4513',
  flood: '#ec4899',
  accident: '#9ca3af'
};


const DISASTER_ICONS = {
  earthquake: '🏚️',
  typhoon: '🌀',
  landslide: '⛰️',
  flood: '🌊',
  accident: '🚧'
};


const PIN_ONLY_TYPES = ['accident'];


function initMap() {
  map = L.map('map', {
    maxBounds: ITOGON_BOUNDS,
    maxBoundsViscosity: 1.0,
    worldCopyJump: false
  }).setView([ITOGON_CENTER.lat, ITOGON_CENTER.lng], 12);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap contributors',
    noWrap: true
  }).addTo(map);

  try {
    const minZoom = map.getBoundsZoom(ITOGON_BOUNDS, true);
    map.setMinZoom(minZoom);
    map.fitBounds(ITOGON_BOUNDS);
  } catch (e) {
    console.warn('Could not compute min zoom for bounds', e);
  }

  map.on('click', (e) => {
    if (!ITOGON_BOUNDS.contains(e.latlng)) {
      alert('Please select a location inside Itogon municipality.');
      return;
    }
    const { lat, lng } = e.latlng;
    setLatLng(lat, lng);
    drawPreview();
  });

  const form = document.getElementById('disaster-form');
  const btnGeocode = document.getElementById('btn-geocode');
  const btnReset = document.getElementById('btn-reset');
  const radiusInput = document.getElementById('radius_m');
  const radiusOut = document.getElementById('radius_m_output');
  const typeSelect = document.getElementById('type');


  typeSelect.addEventListener('change', () => {
    const selectedType = typeSelect.value;
    const radiusField = document.querySelector('.field:has(#radius_m)');
    

    if (PIN_ONLY_TYPES.includes(selectedType)) {
      radiusField.style.display = 'none';
      radiusInput.value = 50; 
    } else {
      radiusField.style.display = 'flex';
      if (radiusInput.value == 50) {
        radiusInput.value = 250; 
        radiusOut.textContent = '250 m';
      }
    }
    
    drawPreview();
  });

  radiusInput.addEventListener('input', () => {
    radiusOut.textContent = `${radiusInput.value} m`;
    drawPreview();
  });

  btnGeocode.addEventListener("click", async () => {
    const address = document.getElementById("address").value.trim();
    if (!address) {
      alert("⚠️ Please enter an address or area to locate.");
      return;
    }

    try {
      const url = `geocode_proxy.php?q=${encodeURIComponent(address + ', Itogon, Benguet')}`;
      const response = await fetch(url, { headers: { "Accept": "application/json" } });
      if (!response.ok) throw new Error("Request failed");

      const data = await response.json();
      if (!data || data.length === 0) {
        alert("No location found. Try a more specific address within Itogon.");
        return;
      }

      const { lat, lon, display_name } = data[0];
      const latitude = parseFloat(lat);
      const longitude = parseFloat(lon);

      document.getElementById("latitude").value = latitude.toFixed(6);
      document.getElementById("longitude").value = longitude.toFixed(6);

      map.setView([latitude, longitude], 15);
      drawPreview();

      if (window.tempMarker) map.removeLayer(window.tempMarker);
      window.tempMarker = L.marker([latitude, longitude])
        .addTo(map)
        .bindPopup(`📍 ${display_name}`)
        .openPopup();

    } catch (error) {
      console.error("❌ Geocoding failed:", error);
      alert("Error finding location. Please try again.");
    }
  });

  btnReset.addEventListener('click', () => {
    clearPreview();
    setLatLng(ITOGON_CENTER.lat, ITOGON_CENTER.lng);
    map.setView([ITOGON_CENTER.lat, ITOGON_CENTER.lng], 12);
    editingId = null;
    document.getElementById('submit-btn').textContent = "Save Report";
  });

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const payload = getFormPayload();
    if (!payload) return;

    let endpoint = 'api/create_disaster.php';
    let successMessage = '✅ Report saved successfully.';
    
    if (editingId !== null) {
      payload.id = editingId;
      endpoint = 'api/update_disaster.php';
      successMessage = '✏️ Report updated successfully.';
    }

    try {
      const res = await fetch(endpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });

      const data = await res.json();
      if (!res.ok || !data.success) throw new Error(data.message || 'Failed to save');

      form.reset();
      document.getElementById('radius_m_output').textContent = '250 m';
      clearPreview();
      await loadDisasters();
      alert(successMessage);

      editingId = null;
      document.getElementById('submit-btn').textContent = "Save Report";

    } catch (err) {
      console.error(err);
      alert('Error: ' + (err.message || 'Unknown error'));
    }
  });

  // Connect new filter controls (removed old filter form event listener)
  const btnApplyFilters = document.getElementById('btn-apply-filters');
  const btnClearFilters = document.getElementById('btn-clear-filters');
  
  if (btnApplyFilters) {
    btnApplyFilters.addEventListener('click', async () => {
      await loadDisasters();
    });
  }
  
  if (btnClearFilters) {
    btnClearFilters.addEventListener('click', async () => {
      document.getElementById('filter_type').value = '';
      document.getElementById('from').value = '';
      document.getElementById('to').value = '';
      document.getElementById('q').value = '';
      await loadDisasters();
    });
  }

  setLatLng(ITOGON_CENTER.lat, ITOGON_CENTER.lng);
  drawPreview();
  loadDisasters();
}


function setLatLng(lat, lng) {
  document.getElementById('latitude').value = lat.toFixed(6);
  document.getElementById('longitude').value = lng.toFixed(6);
}

function drawPreview() {
  let lat = parseFloat(document.getElementById('latitude').value);
  let lng = parseFloat(document.getElementById('longitude').value);

  if (!ITOGON_BOUNDS.contains([lat, lng])) {
    const c = clampToBounds(lat, lng);
    lat = c.lat; lng = c.lng;
    setLatLng(lat, lng);
  }

  const radius = parseFloat(document.getElementById('radius_m').value || '250');
  const type = document.getElementById('type').value || 'flood';
  const color = TYPE_COLORS[type] || '#2563eb';
  const icon = DISASTER_ICONS[type] || '📍';
  const isPinOnly = PIN_ONLY_TYPES.includes(type);


  const customIcon = L.divIcon({
    html: `<div style="font-size: 32px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">${icon}</div>`,
    className: 'custom-disaster-icon',
    iconSize: [40, 40],
    iconAnchor: [20, 40]
  });

  if (!previewMarker) {
    previewMarker = L.marker([lat, lng], { 
      icon: customIcon,
      draggable: true 
    }).addTo(map);
    
    previewMarker.on('dragend', (e) => {
      let p = e.target.getLatLng();
      if (!ITOGON_BOUNDS.contains(p)) {
        const clamped = clampToBounds(p.lat, p.lng);
        p = L.latLng(clamped.lat, clamped.lng);
        previewMarker.setLatLng(p);
      }
      setLatLng(p.lat, p.lng);
      drawPreview();
    });
  } else {
    previewMarker.setLatLng([lat, lng]);
    previewMarker.setIcon(customIcon);
  }


  if (!isPinOnly) {
    if (!previewCircle) {
      previewCircle = L.circle([lat, lng], {
        radius,
        color,
        weight: 2,
        opacity: 0.9,
        fillColor: color,
        fillOpacity: 0.18
      }).addTo(map);
    } else {
      previewCircle.setLatLng([lat, lng]);
      previewCircle.setRadius(radius);
      previewCircle.setStyle({ color, fillColor: color });
    }
  } else {

    if (previewCircle) {
      previewCircle.remove();
      previewCircle = null;
    }
  }
}

function clearPreview() {
  if (previewCircle) { previewCircle.remove(); previewCircle = null; }
  if (previewMarker) { previewMarker.remove(); previewMarker = null; }
}

function clampToBounds(lat, lng) {
  const sw = ITOGON_BOUNDS.getSouthWest();
  const ne = ITOGON_BOUNDS.getNorthEast();
  const south = sw.lat, north = ne.lat, west = sw.lng, east = ne.lng;
  const clampedLat = Math.min(Math.max(lat, south), north);
  const clampedLng = Math.min(Math.max(lng, west), east);
  return { lat: clampedLat, lng: clampedLng };
}


function getFormPayload() {
  const name = document.getElementById('name').value.trim();
  const type = document.getElementById('type').value;
  const occurred_at = document.getElementById('occurred_at').value;
  const intensity_signal = document.getElementById('intensity_signal').value.trim();
  const address = document.getElementById('address').value.trim();
  const description = document.getElementById('description').value.trim();
  const lat = parseFloat(document.getElementById('latitude').value);
  const lng = parseFloat(document.getElementById('longitude').value);
  const radius_m = parseFloat(document.getElementById('radius_m').value);

  if (!name || !type || !occurred_at || !intensity_signal ||
      Number.isNaN(lat) || Number.isNaN(lng) || Number.isNaN(radius_m)) {
    alert('Please fill in all required fields.');
    return null;
  }

  if (!ITOGON_BOUNDS.contains([lat, lng])) {
    alert('Location must be inside Itogon municipality.');
    return null;
  }

  return {
    name,
    type,
    occurred_at,
    intensity_signal,
    latitude: lat,
    longitude: lng,
    radius_m,
    address,
    description
  };
}

async function loadDisasters() {
  layers.forEach(l => l.remove());
  layers = [];
  document.getElementById('report-list').innerHTML = '';

  const params = new URLSearchParams();
  const fType = document.getElementById('filter_type').value;
  const fFrom = document.getElementById('from').value;
  const fTo = document.getElementById('to').value;
  const q = document.getElementById('q').value.trim();

  if (fType) params.set('type', fType);
  if (fFrom) params.set('from', fFrom);
  if (fTo) params.set('to', fTo);
  if (q) params.set('q', q);

  const res = await fetch('api/list_disasters.php?' + params.toString());
  const data = await res.json();

  if (!res.ok || !data.success) {
    console.error(data);
    alert('Failed to load reports');
    return;
  }

  const list = data.data || [];
  const bounds = L.latLngBounds();

  list.forEach((d) => {
    const color = TYPE_COLORS[d.type] || '#2563eb';
    const icon = DISASTER_ICONS[d.type] || '📍';
    const lat = parseFloat(d.latitude), lng = parseFloat(d.longitude);
    const center = [lat, lng];
    const isPinOnly = PIN_ONLY_TYPES.includes(d.type);


    let circle = null;
    if (!isPinOnly) {
      circle = L.circle(center, {
        radius: parseFloat(d.radius_m),
        color,
        weight: 2,
        opacity: 0.9,
        fillColor: color,
        fillOpacity: 0.12
      }).addTo(map);
      layers.push(circle);
    }


    const customIcon = L.divIcon({
      html: `<div style="font-size: 32px; text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">${icon}</div>`,
      className: 'custom-disaster-icon',
      iconSize: [40, 40],
      iconAnchor: [20, 40]
    });

    const marker = L.marker(center, { icon: customIcon }).addTo(map);

    const html = `
      <div class="infowin">
        <strong>${escapeHtml(d.name)}</strong><br />
        <small>${escapeHtml(d.type)} • ${formatDateTime(d.occurred_at)}</small><br />
        <div>${escapeHtml(d.intensity_signal)}</div>
        ${d.address ? `<div>${escapeHtml(d.address)}</div>` : ''}
        ${d.description ? `<div><em>${escapeHtml(d.description)}</em></div>` : ''}
        ${!isPinOnly ? `<div>Affected Radius: ${Number(d.radius_m).toLocaleString()} m</div>` : ''}

        <div style="margin-top: 10px; display: flex; gap: 6px;">
          <button class="edit-btn" onclick="window.startEdit(${d.id})" style="flex: 1; padding: 6px 10px; background: #f59e0b; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px;">✏️ Edit</button>
          <button class="delete-btn" onclick="window.deleteReport(${d.id})" style="flex: 1; padding: 6px 10px; background: #dc2626; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px;">🗑️ Delete</button>
        </div>
      </div>
    `;

    if (circle) circle.bindPopup(html);
    marker.bindPopup(html);

    layers.push(marker);
    bounds.extend(center);

    const li = document.createElement('li');
    li.className = 'report-item';
    li.innerHTML = `
      <div style="display: flex; align-items: start; gap: 10px;">
        <div style="font-size: 28px; flex-shrink: 0;">${icon}</div>
        <div style="flex: 1;">
          <h3>${escapeHtml(d.name)}</h3>
          <div class="meta">${escapeHtml(d.type)} • ${formatDateTime(d.occurred_at)}${!isPinOnly ? ' • ' + Number(d.radius_m).toLocaleString() + ' m' : ''}</div>
          ${d.address ? `<div>${escapeHtml(d.address)}</div>` : ''}
          ${d.description ? `<div>${escapeHtml(d.description)}</div>` : ''}
        </div>
      </div>

      <div class="actions">
        <button class="secondary" data-goto="${d.id}">View</button>
        <button class="edit-btn" data-edit="${d.id}">Edit</button>
        <button class="delete-btn" data-delete="${d.id}">Delete</button>
        <button class="print-btn" data-print="${d.id}">🖨️ Print</button>
      </div>
    `;

    li.querySelector('button[data-goto]').addEventListener('click', () => {
      map.setView(center, 15);
      if (circle) {
        circle.openPopup();
      } else {
        marker.openPopup();
      }
    });

    li.querySelector('button[data-edit]').addEventListener('click', () => startEdit(d.id));
    li.querySelector('button[data-delete]').addEventListener('click', () => deleteReport(d.id));
    li.querySelector('button[data-print]').addEventListener('click', () => printReport(d));

    document.getElementById('report-list').appendChild(li);
  });

  if (list.length > 0) {
    try { map.fitBounds(bounds.pad(0.1)); } catch (e) { console.warn(e); }
  }
}


async function startEdit(id) {
  const res = await fetch('api/list_disasters.php');
  const data = await res.json();
  
  if (!data.success) {
    alert('Failed to load disaster data');
    return;
  }

  const d = data.data.find(item => item.id == id);
  if (!d) {
    alert('Report not found');
    return;
  }

  editingId = d.id;

  document.getElementById('name').value = d.name;
  document.getElementById('type').value = d.type;
  document.getElementById('occurred_at').value = d.occurred_at.replace(' ', 'T');
  document.getElementById('intensity_signal').value = d.intensity_signal;
  document.getElementById('address').value = d.address || '';
  document.getElementById('description').value = d.description || '';
  document.getElementById('radius_m').value = d.radius_m;
  document.getElementById('radius_m_output').textContent = `${d.radius_m} m`;


  const radiusField = document.querySelector('.field:has(#radius_m)');
  if (PIN_ONLY_TYPES.includes(d.type)) {
    radiusField.style.display = 'none';
  } else {
    radiusField.style.display = 'flex';
  }

  setLatLng(parseFloat(d.latitude), parseFloat(d.longitude));
  drawPreview();

  document.getElementById('submit-btn').textContent = "Update Report";
  window.scrollTo({ top: 0, behavior: "smooth" });
}


async function deleteReport(id) {
  if (!confirm("⚠️ Are you sure you want to delete this report? This action cannot be undone.")) return;

  try {
    const res = await fetch('api/delete_disaster.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    });

    const data = await res.json();
    if (!data.success) throw new Error(data.message);

    alert("🗑️ Report deleted successfully.");
    await loadDisasters();

  } catch (err) {
    console.error(err);
    alert("Error deleting report: " + err.message);
  }
}


function printReport(report) {
  const icon = DISASTER_ICONS[report.type] || '📍';
  const color = TYPE_COLORS[report.type] || '#2563eb';
  const isPinOnly = PIN_ONLY_TYPES.includes(report.type);
  
  const printWindow = window.open('', '_blank');
  printWindow.document.write(`
    <!DOCTYPE html>
    <html>
    <head>
      <title>Disaster Report - ${escapeHtml(report.name)}</title>
      <style>
        body {
          font-family: Arial, sans-serif;
          max-width: 800px;
          margin: 40px auto;
          padding: 20px;
          color: #333;
        }
        .header {
          text-align: center;
          border-bottom: 3px solid ${color};
          padding-bottom: 20px;
          margin-bottom: 30px;
        }
        .header h1 {
          color: ${color};
          margin: 0;
          font-size: 28px;
        }
        .header .subtitle {
          color: #666;
          margin-top: 5px;
        }
        .icon {
          font-size: 60px;
          margin: 20px 0;
        }
        .section {
          margin: 20px 0;
          padding: 15px;
          background: #f9f9f9;
          border-left: 4px solid ${color};
        }
        .section h2 {
          margin: 0 0 10px 0;
          color: ${color};
          font-size: 18px;
        }
        .field {
          margin: 10px 0;
          display: grid;
          grid-template-columns: 200px 1fr;
          gap: 10px;
        }
        .field-label {
          font-weight: bold;
          color: #555;
        }
        .footer {
          margin-top: 40px;
          padding-top: 20px;
          border-top: 1px solid #ddd;
          text-align: center;
          color: #888;
          font-size: 12px;
        }
        @media print {
          body { margin: 0; }
          .no-print { display: none; }
        }
      </style>
    </head>
    <body>
      <div class="header">
        <h1>Itogon Disaster Alert System</h1>
        <div class="subtitle">Municipality of Itogon, Benguet Province</div>
        <div class="icon">${icon}</div>
      </div>

      <div class="section">
        <h2>Disaster Report Details</h2>
        <div class="field">
          <div class="field-label">Report Name:</div>
          <div>${escapeHtml(report.name)}</div>
        </div>
        <div class="field">
          <div class="field-label">Disaster Type:</div>
          <div style="text-transform: capitalize;">${escapeHtml(report.type)}</div>
        </div>
        <div class="field">
          <div class="field-label">Date & Time Occurred:</div>
          <div>${formatDateTime(report.occurred_at)}</div>
        </div>
        <div class="field">
          <div class="field-label">Intensity/Signal:</div>
          <div>${escapeHtml(report.intensity_signal)}</div>
        </div>
      </div>

      <div class="section">
        <h2>Location Information</h2>
        <div class="field">
          <div class="field-label">Address/Area:</div>
          <div>${escapeHtml(report.address || 'Not specified')}</div>
        </div>
        <div class="field">
          <div class="field-label">Coordinates:</div>
          <div>Latitude: ${report.latitude}°, Longitude: ${report.longitude}°</div>
        </div>
        ${!isPinOnly ? `
        <div class="field">
          <div class="field-label">Affected Radius:</div>
          <div>${Number(report.radius_m).toLocaleString()} meters</div>
        </div>
        ` : ''}
      </div>

      ${report.description ? `
      <div class="section">
        <h2>Additional Notes</h2>
        <p>${escapeHtml(report.description)}</p>
      </div>
      ` : ''}

      <div class="footer">
        <p>Generated on ${new Date().toLocaleString()}</p>
        <p>Itogon Disaster Risk Response Map System</p>
      </div>

      <div class="no-print" style="text-align: center; margin-top: 30px;">
        <button onclick="window.print()" style="padding: 10px 30px; background: ${color}; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">🖨️ Print Report</button>
        <button onclick="window.close()" style="padding: 10px 30px; background: #666; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin-left: 10px;">✖ Close</button>
      </div>
    </body>
    </html>
  `);
  printWindow.document.close();
}


window.startEdit = startEdit;
window.deleteReport = deleteReport;
window.printReport = printReport;


function escapeHtml(s) {
  return String(s || '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#39;');
}

function formatDateTime(dt) {
  try {
    const d = new Date(dt.replace(' ', 'T'));
    return d.toLocaleString();
  } catch {
    return dt;
  }
}


document.addEventListener('DOMContentLoaded', initMap);
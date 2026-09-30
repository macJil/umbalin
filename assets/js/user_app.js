let map;
let layers = [];
let userPreviewCircle, userPreviewMarker;

const ITOGON_CENTER = { lat: 16.3667, lng: 120.6833 };
const ITOGON_BOUNDS = L.latLngBounds([[16.20, 120.52],[16.52, 120.76]]);

const TYPE_COLORS = {
  earthquake: '#ef4444',
  typhoon: '#8b5cf6',
  landslide: '#8B4513',
  flood: '#ec4899',
  accident: '#f59e0b'
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
  map = L.map('map', { maxBounds: ITOGON_BOUNDS, maxBoundsViscosity: 1.0 }).setView([ITOGON_CENTER.lat, ITOGON_CENTER.lng], 12);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap contributors',
    noWrap: true
  }).addTo(map);

  try {
    const minZoom = map.getBoundsZoom(ITOGON_BOUNDS, true);
    map.setMinZoom(minZoom);
    map.fitBounds(ITOGON_BOUNDS);
  } catch (e) { console.warn(e); }


  document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const tab = btn.dataset.tab;
      

      document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      

      document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
      document.getElementById(`${tab}-tab`).classList.add('active');
      

      if (tab === 'my-reports') {
        loadMyReports();
      } else if (tab === 'view') {
        loadDisasters();
      }
    });
  });


  // Connect new filter controls (replacing old filter form)
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


  setupUserReportForm();

  loadDisasters();
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
      <div class="actions"><button class="secondary" data-goto="${d.id}">View</button></div>
    `;

    li.querySelector('button[data-goto]').addEventListener('click', () => {
      map.setView(center, 15);
      if (circle) {
        circle.openPopup();
      } else {
        marker.openPopup();
      }
    });

    document.getElementById('report-list').appendChild(li);
  });

  if (list.length > 0) {
    try { map.fitBounds(bounds.pad(0.1)); } catch (e) { console.warn(e); }
  }
}


function setupUserReportForm() {
  const form = document.getElementById('user-disaster-form');
  const typeSelect = document.getElementById('user_type');
  const radiusInput = document.getElementById('user_radius_m');
  const radiusOut = document.getElementById('user_radius_m_output');
  const radiusField = document.getElementById('user-radius-field');
  const btnGeocode = document.getElementById('user-btn-geocode');
  const btnReset = document.getElementById('user-btn-reset');


  map.on('click', (e) => {
    if (!ITOGON_BOUNDS.contains(e.latlng)) {
      alert('Please select a location inside Itogon municipality.');
      return;
    }
    const { lat, lng } = e.latlng;
    setUserLatLng(lat, lng);
    drawUserPreview();
  });


  typeSelect.addEventListener('change', () => {
    const selectedType = typeSelect.value;
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
    drawUserPreview();
  });


  radiusInput.addEventListener('input', () => {
    radiusOut.textContent = `${radiusInput.value} m`;
    drawUserPreview();
  });


  btnGeocode.addEventListener('click', async () => {
    const address = document.getElementById('user_address').value.trim();
    if (!address) {
      alert('⚠️ Please enter an address or area to locate.');
      return;
    }

    try {
      const url = `geocode_proxy.php?q=${encodeURIComponent(address + ', Itogon, Benguet')}`;
      const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
      if (!response.ok) throw new Error('Request failed');

      const data = await response.json();
      if (!data || data.length === 0) {
        alert('No location found. Try a more specific address within Itogon.');
        return;
      }

      const { lat, lon } = data[0];
      const latitude = parseFloat(lat);
      const longitude = parseFloat(lon);

      setUserLatLng(latitude, longitude);
      map.setView([latitude, longitude], 15);
      drawUserPreview();

    } catch (error) {
      console.error('❌ Geocoding failed:', error);
      alert('Error finding location. Please try again.');
    }
  });


  btnReset.addEventListener('click', () => {
    clearUserPreview();
    setUserLatLng(ITOGON_CENTER.lat, ITOGON_CENTER.lng);
    map.setView([ITOGON_CENTER.lat, ITOGON_CENTER.lng], 12);
  });


  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const payload = {
      name: document.getElementById('user_name').value.trim(),
      type: document.getElementById('user_type').value,
      occurred_at: document.getElementById('user_occurred_at').value,
      intensity_signal: document.getElementById('user_intensity_signal').value.trim(),
      address: document.getElementById('user_address').value.trim(),
      description: document.getElementById('user_description').value.trim(),
      latitude: parseFloat(document.getElementById('user_latitude').value),
      longitude: parseFloat(document.getElementById('user_longitude').value),
      radius_m: parseFloat(document.getElementById('user_radius_m').value)
    };

    if (!payload.name || !payload.type || !payload.occurred_at || !payload.intensity_signal || !payload.description) {
      alert('Please fill in all required fields.');
      return;
    }

    if (!ITOGON_BOUNDS.contains([payload.latitude, payload.longitude])) {
      alert('Location must be inside Itogon municipality.');
      return;
    }

    try {
      const res = await fetch('api/submit_user_report.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });

      const data = await res.json();
      if (!res.ok || !data.success) throw new Error(data.message || 'Failed to submit');

      alert('✅ Report submitted successfully! An administrator will review it shortly.');
      form.reset();
      radiusOut.textContent = '250 m';
      clearUserPreview();
      

      document.querySelector('.tab-btn[data-tab="my-reports"]').click();

    } catch (err) {
      console.error(err);
      alert('Error: ' + (err.message || 'Unknown error'));
    }
  });

  setUserLatLng(ITOGON_CENTER.lat, ITOGON_CENTER.lng);
}

function setUserLatLng(lat, lng) {
  document.getElementById('user_latitude').value = lat.toFixed(6);
  document.getElementById('user_longitude').value = lng.toFixed(6);
}

function drawUserPreview() {
  let lat = parseFloat(document.getElementById('user_latitude').value);
  let lng = parseFloat(document.getElementById('user_longitude').value);

  if (!ITOGON_BOUNDS.contains([lat, lng])) {
    const c = clampToBounds(lat, lng);
    lat = c.lat; lng = c.lng;
    setUserLatLng(lat, lng);
  }

  const radius = parseFloat(document.getElementById('user_radius_m').value || '250');
  const type = document.getElementById('user_type').value || 'flood';
  const color = TYPE_COLORS[type] || '#2563eb';
  const icon = DISASTER_ICONS[type] || '📍';
  const isPinOnly = PIN_ONLY_TYPES.includes(type);

  const customIcon = L.divIcon({
    html: `<div style="font-size: 32px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">${icon}</div>`,
    className: 'custom-disaster-icon',
    iconSize: [40, 40],
    iconAnchor: [20, 40]
  });

  if (!userPreviewMarker) {
    userPreviewMarker = L.marker([lat, lng], { icon: customIcon, draggable: true }).addTo(map);
    userPreviewMarker.on('dragend', (e) => {
      let p = e.target.getLatLng();
      if (!ITOGON_BOUNDS.contains(p)) {
        const clamped = clampToBounds(p.lat, p.lng);
        p = L.latLng(clamped.lat, clamped.lng);
        userPreviewMarker.setLatLng(p);
      }
      setUserLatLng(p.lat, p.lng);
      drawUserPreview();
    });
  } else {
    userPreviewMarker.setLatLng([lat, lng]);
    userPreviewMarker.setIcon(customIcon);
  }

  if (!isPinOnly) {
    if (!userPreviewCircle) {
      userPreviewCircle = L.circle([lat, lng], {
        radius, color, weight: 2, opacity: 0.9, fillColor: color, fillOpacity: 0.18
      }).addTo(map);
    } else {
      userPreviewCircle.setLatLng([lat, lng]);
      userPreviewCircle.setRadius(radius);
      userPreviewCircle.setStyle({ color, fillColor: color });
    }
  } else {
    if (userPreviewCircle) {
      userPreviewCircle.remove();
      userPreviewCircle = null;
    }
  }
}

function clearUserPreview() {
  if (userPreviewCircle) { userPreviewCircle.remove(); userPreviewCircle = null; }
  if (userPreviewMarker) { userPreviewMarker.remove(); userPreviewMarker = null; }
}

function clampToBounds(lat, lng) {
  const sw = ITOGON_BOUNDS.getSouthWest();
  const ne = ITOGON_BOUNDS.getNorthEast();
  const south = sw.lat, north = ne.lat, west = sw.lng, east = ne.lng;
  const clampedLat = Math.min(Math.max(lat, south), north);
  const clampedLng = Math.min(Math.max(lng, west), east);
  return { lat: clampedLat, lng: clampedLng };
}


async function loadMyReports() {
  const list = document.getElementById('my-reports-list');
  list.innerHTML = '<li style="padding: 20px; text-align: center; color: #6b7280;">Loading...</li>';

  try {
    const res = await fetch('api/list_user_reports.php');
    

    if (!res.ok) {
      throw new Error(`HTTP error! status: ${res.status}`);
    }
    

    const text = await res.text();
    if (!text) {
      throw new Error('Empty response from server');
    }
    

    let data;
    try {
      data = JSON.parse(text);
    } catch (e) {
      console.error('Response text:', text);
      throw new Error('Invalid JSON response: ' + e.message);
    }

    if (!data.success) {
      list.innerHTML = `<li style="padding: 20px; text-align: center; color: #dc2626;">Error: ${escapeHtml(data.message || 'Failed to load reports')}</li>`;
      return;
    }

    const reports = data.data || [];

    if (reports.length === 0) {
      list.innerHTML = '<li style="padding: 20px; text-align: center; color: #6b7280;">No reports submitted yet</li>';
      return;
    }

    list.innerHTML = '';

    reports.forEach(r => {
      const icon = DISASTER_ICONS[r.type] || '📍';
      const statusClass = `status-${r.status}`;
      const statusText = r.status.charAt(0).toUpperCase() + r.status.slice(1);
      const color = TYPE_COLORS[r.type] || '#2563eb';
      const isPinOnly = PIN_ONLY_TYPES.includes(r.type);

      const li = document.createElement('li');
      li.className = 'report-item';
      li.innerHTML = `
        <div style="display: flex; align-items: start; gap: 10px;">
          <div style="font-size: 28px; flex-shrink: 0;">${icon}</div>
          <div style="flex: 1;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
              <h3>${escapeHtml(r.name)}</h3>
              <span class="status-badge ${statusClass}">${statusText}</span>
            </div>
            <div class="meta">${escapeHtml(r.type)} • ${formatDateTime(r.submitted_at)}</div>
            ${r.address ? `<div style="font-size: 12px; color: #6b7280;">${escapeHtml(r.address)}</div>` : ''}
            ${r.description ? `<div style="font-size: 13px; margin-top: 4px;">${escapeHtml(r.description)}</div>` : ''}
            ${r.admin_notes ? `<div style="margin-top: 8px; padding: 8px; background: #fef3c7; border-radius: 4px; font-size: 12px;"><strong>Admin note:</strong> ${escapeHtml(r.admin_notes)}</div>` : ''}
            
            <!-- Add Print Button -->
            <div class="actions" style="margin-top: 10px;">
              <button class="print-btn" data-print="${r.id}" style="background: #3b82f6; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 12px;">
                🖨️ Print Report
              </button>
            </div>
          </div>
        </div>
      `;


      const printBtn = li.querySelector('.print-btn');
      if (printBtn) {
        printBtn.addEventListener('click', () => printUserReport(r, color, icon, isPinOnly));
      }

      list.appendChild(li);
    });

  } catch (err) {
    console.error('Error loading my reports:', err);
    list.innerHTML = `<li style="padding: 20px; text-align: center; color: #dc2626;">Error: ${escapeHtml(err.message)}<br><small>Check browser console for details</small></li>`;
  }
}


function printUserReport(report, color, icon, isPinOnly) {
  const statusMap = {
    'pending': '⏳ Pending Review',
    'approved': '✅ Approved',
    'rejected': '❌ Rejected'
  };
  
  const statusText = statusMap[report.status] || report.status;
  const statusColor = report.status === 'approved' ? '#10b981' : 
                     report.status === 'rejected' ? '#ef4444' : 
                     '#f59e0b';
  
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
        .status-display {
          display: inline-block;
          padding: 6px 12px;
          border-radius: 4px;
          font-weight: bold;
          font-size: 14px;
          background: ${statusColor};
          color: white;
          margin-bottom: 15px;
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
        <div class="status-display">${statusText}</div>
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
        <div class="field">
          <div class="field-label">Submission Date:</div>
          <div>${formatDateTime(report.submitted_at)}</div>
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

      <div class="section">
        <h2>Report Description</h2>
        <p>${escapeHtml(report.description || 'No description provided')}</p>
      </div>

      ${report.admin_notes ? `
      <div class="section">
        <h2>Administrator Review</h2>
        <div style="padding: 10px; background: #fef3c7; border-radius: 4px;">
          ${escapeHtml(report.admin_notes)}
        </div>
      </div>
      ` : ''}

      <div class="footer">
        <p>Generated on ${new Date().toLocaleString()}</p>
        <p>Itogon Disaster Risk Response Map System - User Report</p>
        <p>Report ID: ${report.id} | Submitted by: ${escapeHtml(report.submitted_by || 'User')}</p>
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

window.printUserReport = printUserReport;
window.escapeHtml = escapeHtml;
window.formatDateTime = formatDateTime;

document.addEventListener('DOMContentLoaded', initMap);
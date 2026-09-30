<?php
session_start();


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
  header("Location: login.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Itogon, Benguet – Disaster Viewer</title>
  <link rel="stylesheet" href="assets/css/styles.css" />
  <link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
    crossorigin=""
  />
  <style>
    #map { width: 100%; height: 100vh; min-height: 520px; position: relative; }
    main { display: flex; gap: 12px; }
    .sidebar { width: 360px; max-width: 40%; overflow-y: auto; padding: 12px; }
    .map-section { flex: 1; position: relative; }
    
    /* Map Legend Styles */
    .map-legend {
      position: absolute;
      top: 10px;
      right: 10px;
      background: white;
      padding: 15px;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      z-index: 1000;
      max-width: 300px;
      max-height: 80vh;
      overflow-y: auto;
    }
    
    .map-legend h3 {
      margin: 0 0 10px 0;
      font-size: 14px;
      color: #374151;
      border-bottom: 1px solid #e5e7eb;
      padding-bottom: 5px;
    }
    
    .legend-item {
      display: flex;
      align-items: center;
      margin: 8px 0;
      font-size: 12px;
    }
    
    .legend-dot {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      margin-right: 8px;
      flex-shrink: 0;
    }
    
    .dot-earthquake { background-color: #ef4444; }
    .dot-typhoon { background-color: #8b5cf6; }
    .dot-landslide { background-color: #8B4513; }
    .dot-flood { background-color: #ec4899; }
    .dot-accident { background-color: #f59e0b; }
    
  
    .filter-controls {
      margin-top: 15px;
      border-top: 1px solid #e5e7eb;
      padding-top: 15px;
    }
    
    .filter-controls .field {
      margin-bottom: 10px;
    }
    
    .filter-controls label {
      font-size: 11px;
      font-weight: 600;
      color: #4b5563;
      display: block;
      margin-bottom: 3px;
    }
    
    .filter-controls select,
    .filter-controls input {
      width: 100%;
      padding: 6px 8px;
      border: 1px solid #d1d5db;
      border-radius: 4px;
      font-size: 12px;
    }
    
    .filter-actions {
      display: flex;
      gap: 8px;
      margin-top: 10px;
    }
    
    .filter-actions button {
      flex: 1;
      padding: 6px 10px;
      font-size: 11px;
    }
    
    .filter-actions .link {
      background: transparent;
      color: #6b7280;
      text-decoration: underline;
      border: none;
      cursor: pointer;
      font-size: 11px;
    }
    
    
    .sidebar .legend {
      display: none;
    }
    
    header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px 20px;
      background: #f8fafc;
      border-bottom: 1px solid #e5e7eb;
    }
    
    .user-info {
      display: flex;
      align-items: center;
      gap: 15px;
      font-size: 14px;
      color: #374151;
    }
    
    .logout-btn {
      background-color: #dc2626;
      color: white;
      border: none;
      padding: 8px 14px;
      border-radius: 6px;
      font-size: 0.9rem;
      cursor: pointer;
      text-decoration: none;
      transition: background 0.2s;
    }
    
    .logout-btn:hover {
      background-color: #b91c1c;
    }

    .tab-buttons {
      display: flex;
      gap: 8px;
      margin-bottom: 12px;
      border-bottom: 2px solid #e5e7eb;
    }

    .tab-btn {
      padding: 10px 20px;
      background: transparent;
      border: none;
      border-bottom: 2px solid transparent;
      cursor: pointer;
      font-weight: 500;
      color: #6b7280;
      transition: all 0.3s ease;
      margin-bottom: -2px;
    }

    .tab-btn.active {
      color: #2563eb;
      border-bottom-color: #2563eb;
    }

    .tab-btn:hover {
      color: #1d4ed8;
    }

    .tab-content {
      display: none;
    }

    .tab-content.active {
      display: block;
    }

    .status-badge {
      display: inline-block;
      padding: 4px 8px;
      border-radius: 4px;
      font-size: 11px;
      font-weight: 600;
      text-transform: uppercase;
    }

    .status-pending {
      background: #fef3c7;
      color: #92400e;
    }

    .status-approved {
      background: #d1fae5;
      color: #065f46;
    }

    .status-rejected {
      background: #fee2e2;
      color: #991b1b;
    }
    
    .print-btn {
      background-color: #3b82f6 !important;
      color: white !important;
      border: none;
      padding: 6px 12px;
      border-radius: 4px;
      cursor: pointer;
      font-size: 12px;
      transition: background-color 0.2s;
    }

    .print-btn:hover {
      background-color: #2563eb !important;
    }
  </style>
</head>
<body>
  <header>
    <h1>Itogon, Benguet – Disaster Reports & Danger Zones</h1>
    <div class="user-info">
      Logged in as: <strong><?= htmlspecialchars($_SESSION['username']); ?></strong>
      <a href="logout.php" class="logout-btn">Logout</a>
    </div>
  </header>

  <main>
    <aside class="sidebar">
      <div class="tab-buttons">
        <button class="tab-btn active" data-tab="view">View Reports</button>
        <button class="tab-btn" data-tab="submit">Submit Report</button>
        <button class="tab-btn" data-tab="my-reports">My Reports</button>
      </div>

      
      <div id="view-tab" class="tab-content active">
        <section class="panel">
          <h2>Reports</h2>
          <ul id="report-list" class="report-list"></ul>
        </section>
      </div>

      <div id="submit-tab" class="tab-content">
        <section class="panel">
          <h2>Submit Disaster Report</h2>
          <p style="font-size: 13px; color: #6b7280; margin-bottom: 15px;">
            📝 Your report will be reviewed by an administrator before appearing on the map.
          </p>
          <form id="user-disaster-form">
            <div class="field">
              <label for="user_name">Report Name</label>
              <input type="text" id="user_name" name="name" placeholder="e.g., Landslide at Ucab Road" required />
            </div>

            <div class="field">
              <label for="user_type">Type</label>
              <select id="user_type" name="type" required>
                <option value="">Select type…</option>
                <option value="earthquake">Earthquake</option>
                <option value="typhoon">Typhoon</option>
                <option value="landslide">Landslide</option>
                <option value="flood">Flood</option>
                <option value="accident">Accident</option>
              </select>
            </div>

            <div class="field">
              <label for="user_occurred_at">Date & Time</label>
              <input type="datetime-local" id="user_occurred_at" name="occurred_at" required />
            </div>

            <div class="field">
              <label for="user_intensity_signal">Intensity / Signal / Casualties</label>
              <input type="text" id="user_intensity_signal" name="intensity_signal" placeholder="e.g., Signal #3 or M5.2" required />
            </div>

            <div class="field">
              <label for="user_address">Address / Area</label>
              <input type="text" id="user_address" name="address" placeholder="e.g., Ucab, Itogon" />
              <button type="button" id="user-btn-geocode">Find on Map</button>
            </div>

            <div class="field inline">
              <div>
                <label for="user_latitude">Latitude</label>
                <input type="number" step="any" id="user_latitude" name="latitude" placeholder="16.3667" required />
              </div>
              <div>
                <label for="user_longitude">Longitude</label>
                <input type="number" step="any" id="user_longitude" name="longitude" placeholder="120.6833" required />
              </div>
            </div>

            <div class="field" id="user-radius-field">
              <label for="user_radius_m">Radius (meters)</label>
              <input type="range" id="user_radius_m" name="radius_m" min="50" max="3000" step="50" value="250" />
              <output id="user_radius_m_output">250 m</output>
            </div>

            <div class="field">
              <label for="user_description">Notes</label>
              <textarea id="user_description" name="description" rows="3" placeholder="Describe what happened" required></textarea>
            </div>

            <div class="actions">
              <button type="submit" id="user-submit-btn">Submit Report</button>
              <button type="reset" class="secondary" id="user-btn-reset">Reset</button>
            </div>
          </form>
        </section>
      </div>

  
      <div id="my-reports-tab" class="tab-content">
        <section class="panel">
          <h2>My Submitted Reports</h2>
          <ul id="my-reports-list" class="report-list"></ul>
        </section>
      </div>
    </aside>

    <section class="map-section">
      <div id="map"></div>
      
   
      <div class="map-legend">
        <h3>🗺️ Map Legend & Filters</h3>
        
     
        <div class="legend-item">
          <span class="legend-dot dot-earthquake"></span> Earthquake
        </div>
        <div class="legend-item">
          <span class="legend-dot dot-typhoon"></span> Typhoon
        </div>
        <div class="legend-item">
          <span class="legend-dot dot-landslide"></span> Landslide
        </div>
        <div class="legend-item">
          <span class="legend-dot dot-flood"></span> Flood
        </div>
        <div class="legend-item">
          <span class="legend-dot dot-accident"></span> Accident
        </div>
        
      
        <div class="filter-controls">
          <div class="field">
            <label for="filter_type">Filter by Type</label>
            <select id="filter_type" name="type">
              <option value="">All Types</option>
              <option value="earthquake">Earthquake</option>
              <option value="typhoon">Typhoon</option>
              <option value="landslide">Landslide</option>
              <option value="flood">Flood</option>
              <option value="accident">Accident</option>
            </select>
          </div>
          
          <div class="field">
            <label for="from">From Date</label>
            <input type="date" id="from" name="from" />
          </div>
          
          <div class="field">
            <label for="to">To Date</label>
            <input type="date" id="to" name="to" />
          </div>
          
          <div class="field">
            <label for="q">Search</label>
            <input type="text" id="q" name="q" placeholder="Name or address" />
          </div>
          
          <div class="filter-actions">
            <button type="button" id="btn-apply-filters" class="secondary">Apply Filters</button>
            <button type="button" id="btn-clear-filters" class="link">Clear</button>
          </div>
        </div>
      </div>
    </section>
  </main>

  <script
    src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
    crossorigin=""
  ></script>
  <script src="assets/js/user_app.js"></script>
</body>
</html>
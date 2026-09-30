<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    header("Location: user_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Itogon, Benguet – Disaster Risk Response Map</title>
  <link rel="stylesheet" href="assets/css/styles.css" />
  <style>
    header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background-color: #f8fafc;
      border-bottom: 1px solid #ddd;
      padding: 10px 20px;
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
    .logout-btn:hover { background-color: #b91c1c; }
    .welcome-text { margin-right: 15px; font-weight: 500; color: #374151; }

    #map { width: 100%; height: 100vh; min-height: 520px; position: relative; }
    main { display: flex; gap: 12px; }
    .sidebar { width: 360px; max-width: 40%; overflow-y: auto; padding: 12px; }
    .map-section { flex: 1; position: relative; }
    
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
    

    .print-btn {
      background-color: #3b82f6 !important;
      color: white !important;
    }
    .print-btn:hover {
      background-color: #2563eb !important;
    }
    
    .user-stats {
      display: flex;
      gap: 8px;
      margin-bottom: 15px;
    }
    
    .user-stat-box {
      flex: 1;
      text-align: center;
      padding: 8px;
      border-radius: 6px;
      font-size: 12px;
    }
    
    .stat-admin {
      background: #dbeafe;
      color: #1e40af;
    }
    
    .stat-user {
      background: #f0fdf4;
      color: #10b981;
    }
    
    .stat-total {
      background: #f8fafc;
      color: #6366f1;
    }
    
    .stat-count {
      font-size: 20px;
      font-weight: bold;
      display: block;
    }
    
    .user-list-item {
      display: flex;
      align-items: center;
      padding: 10px;
      border-bottom: 1px solid #e5e7eb;
      background: white;
      transition: background 0.2s;
    }
    
    .user-list-item:hover {
      background: #f9fafb;
    }
    
    .user-avatar {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      font-size: 14px;
      margin-right: 10px;
      flex-shrink: 0;
    }
    
    .user-avatar.admin {
      background: #dbeafe;
      color: #1e40af;
    }
    
    .user-avatar.user {
      background: #f3f4f6;
      color: #4b5563;
    }
    
    .user-info {
      flex: 1;
      min-width: 0;
    }
    
    .user-name {
      font-weight: 600;
      color: #111827;
      font-size: 13px;
      margin-bottom: 2px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    
    .user-details {
      font-size: 11px;
      color: #6b7280;
      display: flex;
      gap: 6px;
      align-items: center;
    }
    
    .user-role-badge {
      display: inline-block;
      padding: 2px 6px;
      border-radius: 10px;
      font-size: 10px;
      font-weight: 600;
      text-transform: uppercase;
    }
    
    .role-admin {
      background: #dbeafe;
      color: #1e40af;
    }
    
    .role-user {
      background: #f3f4f6;
      color: #4b5563;
    }
    
    .user-actions {
      display: flex;
      gap: 4px;
      flex-shrink: 0;
    }
    
    .user-action-btn {
      background: none;
      border: 1px solid #d1d5db;
      border-radius: 4px;
      padding: 4px 8px;
      font-size: 11px;
      cursor: pointer;
      transition: all 0.2s;
    }
    
    .btn-edit-user {
      color: #f59e0b;
      border-color: #fbbf24;
    }
    
    .btn-delete-user {
      color: #dc2626;
      border-color: #fca5a5;
    }
    
    .current-user-indicator {
      background: #f0f9ff;
      border-left: 3px solid #3b82f6;
    }
    
    #user-search {
      width: 100%;
      padding: 8px;
      border: 1px solid #d1d5db;
      border-radius: 6px;
      font-size: 13px;
      margin-bottom: 10px;
    }

    .modal {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.5);
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 1000;
    }
    
    .modal-content {
      background: white;
      padding: 25px;
      border-radius: 8px;
      width: 90%;
      max-width: 400px;
      max-height: 80vh;
      overflow-y: auto;
    }
    
    #password-hint {
      font-size: 12px;
      color: #6b7280;
      font-weight: normal;
    }
  </style>
</head>
<body>
  <header>
    <h1>Itogon, Benguet – Disaster Risk Response Map</h1>
    <div>
      <span class="welcome-text">
        👋 Welcome, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
      </span>
      <a href="logout.php" class="logout-btn" onclick="return confirm('Are you sure you want to log out?');">Logout</a>
    </div>
  </header>

  <main>
    <aside class="sidebar">
      <section class="panel">
        <h2>Report a Disaster</h2>
        <form id="disaster-form">
          <div class="field">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" placeholder="e.g., Kennon Road Flooding" required />
          </div>

          <div class="field">
            <label for="type">Type</label>
            <select id="type" name="type" required>
              <option value="">Select type…</option>
              <option value="earthquake">Earthquake</option>
              <option value="typhoon">Typhoon</option>
              <option value="landslide">Landslide</option>
              <option value="flood">Flood</option>
              <option value="accident">Accident</option>
            </select>
          </div>

          <div class="field">
            <label for="occurred_at">Date & Time</label>
            <input type="datetime-local" id="occurred_at" name="occurred_at" required />
          </div>

          <div class="field">
            <label for="intensity_signal">Intensity / Signal / Casualties</label>
            <input type="text" id="intensity_signal" name="intensity_signal" placeholder="e.g., Signal #3 or M5.2" required />
          </div>

          <div class="field">
            <label for="address">Address / Area</label>
            <input type="text" id="address" name="address" placeholder="e.g., Ampucao, Itogon" />
            <button type="button" id="btn-geocode">Find on Map</button>
          </div>

          <div class="field inline">
            <div>
              <label for="latitude">Latitude</label>
              <input type="number" step="any" id="latitude" name="latitude" placeholder="16.3667" required />
            </div>
            <div>
              <label for="longitude">Longitude</label>
              <input type="number" step="any" id="longitude" name="longitude" placeholder="120.6833" required />
            </div>
          </div>

          <div class="field">
            <label for="radius_m">Radius (meters)</label>
            <input type="range" id="radius_m" name="radius_m" min="50" max="3000" step="50" value="250" />
            <output id="radius_m_output">250 m</output>
          </div>

          <div class="field">
            <label for="description">Notes (optional)</label>
            <textarea id="description" name="description" rows="3" placeholder="Short details or notes"></textarea>
          </div>

          <div class="actions">
            <button type="submit" id="submit-btn">Save Report</button>
            <button type="reset" class="secondary" id="btn-reset">Reset</button>
          </div>
        </form>
      </section>


      <section class="panel">
        <h2>Reports</h2>
        <ul id="report-list" class="report-list"></ul>
      </section>

      <section class="panel">
        <h2>👥 User Management</h2>
        <div style="margin-bottom: 15px; display: flex; gap: 8px;">
          <button type="button" id="btn-add-user" class="secondary" style="flex: 1;">
            ➕ Add User
          </button>
          <button type="button" id="btn-refresh-users" class="secondary" style="flex: 1;">
            🔄 Refresh
          </button>
        </div>
        

        <div class="user-stats">
            <div class="user-stat-box stat-admin">
                <span class="stat-count" id="admin-count">0</span>
                <span>Admins</span>
            </div>
            <div class="user-stat-box stat-user">
                <span class="stat-count" id="user-count">0</span>
                <span>Regular Users</span>
            </div>
            <div class="user-stat-box stat-total">
                <span class="stat-count" id="total-count">0</span>
                <span>Total</span>
            </div>
        </div>
        

        <div style="margin-bottom: 10px;">
          <input type="text" id="user-search" placeholder="🔍 Search users..." 
                 style="width: 100%; padding: 6px 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px;">
        </div>
        

        <div id="user-list-container" style="max-height: 300px; overflow-y: auto; border: 1px solid #e5e7eb; border-radius: 6px;">
          <ul id="user-list" class="report-list" style="margin: 0;"></ul>
        </div>
      </section>

      <section class="panel">
        <h2>📋 Pending User Reports</h2>
        <button type="button" id="btn-refresh-pending" class="secondary" style="width: 100%; margin-bottom: 10px;">
          🔄 Refresh Pending Reports
        </button>
        <ul id="pending-reports-list" class="report-list"></ul>
      </section>

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


<div id="user-modal" class="modal">
    <div class="modal-content">
        <h2 id="modal-title">Add New User</h2>
        <form id="user-form">
            <input type="hidden" id="user-id" value="">
            
            <div class="field">
                <label for="modal-username">Username / Email</label>
                <input type="text" id="modal-username" required placeholder="user@example.com">
            </div>
            
            <div class="field">
                <label for="modal-password">
                    Password <span id="password-hint">(required for new user)</span>
                </label>
                <input type="password" id="modal-password" placeholder="••••••••">
                <div style="font-size: 12px; color: #6b7280; margin-top: 4px;">
                    <input type="checkbox" id="show-password">
                    <label for="show-password" style="cursor: pointer;">Show password</label>
                </div>
            </div>
            
            <div class="actions">
                <button type="submit" id="modal-submit">💾 Save User</button>
                <button type="button" id="modal-cancel" class="secondary">❌ Cancel</button>
            </div>
        </form>
    </div>
</div>

  <link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
    crossorigin=""
  />
  <script
    src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
    crossorigin=""
  ></script>

  <script src="assets/js/app.js"></script>
  
  <script>

  async function loadPendingReports() {
    const list = document.getElementById('pending-reports-list');
    if (!list) return;
    
    list.innerHTML = '<li style="padding: 20px; text-align: center; color: #6b7280;">Loading...</li>';

    try {
      const res = await fetch('api/list_pending_reports.php?status=pending');
      const data = await res.json();

      if (!res.ok || !data.success) {
        list.innerHTML = '<li style="padding: 20px; text-align: center; color: #dc2626;">Failed to load</li>';
        return;
      }

      const reports = data.data || [];

      if (reports.length === 0) {
        list.innerHTML = '<li style="padding: 20px; text-align: center; color: #6b7280;">No pending reports</li>';
        return;
      }

      list.innerHTML = '';

      reports.forEach(r => {
        const icon = window.DISASTER_ICONS ? (window.DISASTER_ICONS[r.type] || '📍') : '📍';
        const isPinOnly = window.PIN_ONLY_TYPES ? window.PIN_ONLY_TYPES.includes(r.type) : false;

        const li = document.createElement('li');
        li.className = 'report-item';
        li.innerHTML = `
          <div style="display: flex; align-items: start; gap: 10px;">
            <div style="font-size: 28px; flex-shrink: 0;">${icon}</div>
            <div style="flex: 1;">
              <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                <h3>${escapeHtml(r.name)}</h3>
                <span class="status-badge status-pending">Pending</span>
              </div>
              <div class="meta">${escapeHtml(r.type)} • ${formatDateTime(r.occurred_at)}${!isPinOnly ? ' • ' + Number(r.radius_m).toLocaleString() + ' m' : ''}</div>
              <div style="font-size: 12px; color: #6b7280; margin-top: 2px;">
                Submitted by: <strong>${escapeHtml(r.submitted_by)}</strong> on ${formatDateTime(r.submitted_at)}
              </div>
              ${r.address ? `<div style="font-size: 12px; color: #6b7280;">📍 ${escapeHtml(r.address)}</div>` : ''}
              ${r.description ? `<div style="font-size: 13px; margin-top: 6px;">${escapeHtml(r.description)}</div>` : ''}
              <div style="font-size: 12px; color: #6b7280; margin-top: 4px;">
                📍 Lat: ${r.latitude}, Lng: ${r.longitude}
              </div>

              <div class="review-actions">
                <button class="btn-approve" data-id="${r.id}" data-action="approve">✅ Approve</button>
                <button class="btn-reject" data-id="${r.id}" data-action="reject">❌ Reject</button>
              </div>
            </div>
          </div>
        `;

        li.querySelector('.btn-approve').addEventListener('click', () => reviewReport(r.id, 'approve'));
        li.querySelector('.btn-reject').addEventListener('click', () => reviewReport(r.id, 'reject'));

        list.appendChild(li);
      });

    } catch (err) {
      console.error(err);
      list.innerHTML = '<li style="padding: 20px; text-align: center; color: #dc2626;">Error loading</li>';
    }
  }

  async function reviewReport(id, action) {
    const actionText = action === 'approve' ? 'approve' : 'reject';
    const confirmMsg = `Are you sure you want to ${actionText} this report?`;
    
    if (!confirm(confirmMsg)) return;

    let adminNotes = '';
    if (action === 'reject') {
      adminNotes = prompt('Optional: Provide a reason for rejection:') || '';
    }

    try {
      const res = await fetch('api/approve_report.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, action, admin_notes: adminNotes })
      });

      const data = await res.json();
      if (!res.ok || !data.success) throw new Error(data.message);

      const message = action === 'approve' 
        ? '✅ Report approved and added to the map!' 
        : '❌ Report rejected.';
      
      alert(message);
      
      await loadPendingReports();
      if (typeof window.loadDisasters === 'function') {
        await window.loadDisasters();
      }

    } catch (err) {
      console.error(err);
      alert('Error: ' + err.message);
    }
  }

  // User Management Functions
  let editingUserId = null;
  let allUsers = [];

  async function loadUsers() {
    const list = document.getElementById('user-list');
    if (!list) return;
    
    list.innerHTML = '<li class="user-list-item" style="justify-content: center; color: #6b7280;">Loading users...</li>';

    try {
      const res = await fetch('api/get_users.php');
      const data = await res.json();

      if (!res.ok || !data.success) {
        list.innerHTML = '<li class="user-list-item" style="justify-content: center; color: #dc2626;">Failed to load users</li>';
        return;
      }

      allUsers = data.data || [];
      const currentUserId = <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'null'; ?>;
      
      updateUserStats(allUsers);
      
      const searchTerm = document.getElementById('user-search')?.value.toLowerCase() || '';
      const filteredUsers = allUsers.filter(user => 
        user.username.toLowerCase().includes(searchTerm) ||
        user.role.toLowerCase().includes(searchTerm) ||
        user.id.toString().includes(searchTerm)
      );

      if (filteredUsers.length === 0) {
        list.innerHTML = `
          <li class="user-list-item" style="justify-content: center; color: #6b7280;">
            ${searchTerm ? 'No users match your search' : 'No users found'}
          </li>
        `;
        return;
      }

      list.innerHTML = '';
      
      filteredUsers.forEach(user => {
        const isCurrentUser = user.id == currentUserId;
        const isPrimaryAdmin = user.id == 1;
        const firstLetter = user.username.charAt(0).toUpperCase();
        const joinDate = new Date(user.created_at).toLocaleDateString('en-US', {
          month: 'short',
          day: 'numeric',
          year: 'numeric'
        });
        
        const li = document.createElement('li');
        li.className = `user-list-item ${isCurrentUser ? 'current-user-indicator' : ''} ${isPrimaryAdmin ? 'protected-user' : ''}`;
        
        li.innerHTML = `
          <div class="user-avatar ${user.role}">
            ${firstLetter}
          </div>
          <div class="user-info">
            <div class="user-name">
              ${escapeHtml(user.username)}
              ${isCurrentUser ? '<span style="font-size: 10px; color: #3b82f6; margin-left: 4px;">(You)</span>' : ''}
            </div>
            <div class="user-details">
              <span class="user-role-badge role-${user.role}">${user.role}</span>
              <span>ID: ${user.id} • ${joinDate}</span>
            </div>
          </div>
          <div class="user-actions">
            ${!isPrimaryAdmin && !isCurrentUser ? `
              <button class="user-action-btn btn-edit-user" data-id="${user.id}" title="Edit User">
                Edit
              </button>
              <button class="user-action-btn btn-delete-user" data-id="${user.id}" title="Delete User">
                Delete
              </button>
            ` : `
              <span style="color: #9ca3af; font-size: 11px;">Protected</span>
            `}
          </div>
        `;

        if (!isPrimaryAdmin && !isCurrentUser) {
          li.querySelector('.btn-edit-user').addEventListener('click', () => editUser(user));
          li.querySelector('.btn-delete-user').addEventListener('click', () => deleteUser(user.id));
        }

        list.appendChild(li);
      });

    } catch (err) {
      console.error('Error loading users:', err);
      list.innerHTML = '<li class="user-list-item" style="justify-content: center; color: #dc2626;">Error loading users</li>';
    }
  }

  function updateUserStats(users) {
    const adminCount = users.filter(u => u.role === 'admin').length;
    const userCount = users.filter(u => u.role === 'user').length;
    const totalCount = users.length;
    
    if (document.getElementById('admin-count')) {
      document.getElementById('admin-count').textContent = adminCount;
    }
    if (document.getElementById('user-count')) {
      document.getElementById('user-count').textContent = userCount;
    }
    if (document.getElementById('total-count')) {
      document.getElementById('total-count').textContent = totalCount;
    }
  }

 function showUserModal(user = null) {
    const modal = document.getElementById('user-modal');
    const title = document.getElementById('modal-title');
    const passwordHint = document.getElementById('password-hint');
    const passwordField = document.getElementById('modal-password');
    
    if (user) {
        // Edit mode
        editingUserId = user.id;
        title.textContent = 'Edit User';
        document.getElementById('user-id').value = user.id;
        document.getElementById('modal-username').value = user.username;
        passwordHint.textContent = '(leave blank to keep current password)';
        passwordField.required = false;
        passwordField.placeholder = '•••••••• (unchanged)';
    } else {
        // Add mode
        editingUserId = null;
        title.textContent = 'Add New User';
        document.getElementById('user-form').reset();
        passwordHint.textContent = '(required for new user)';
        passwordField.required = true;
        passwordField.placeholder = '••••••••';
    }
    
    modal.style.display = 'flex';
}

  function hideUserModal() {
    document.getElementById('user-modal').style.display = 'none';
    editingUserId = null;
    document.getElementById('user-form').reset();
    const passwordField = document.getElementById('modal-password');
    passwordField.type = 'password';
    document.getElementById('show-password').checked = false;
  }

  function togglePassword() {
    const passwordField = document.getElementById('modal-password');
    const showPassword = document.getElementById('show-password').checked;
    passwordField.type = showPassword ? 'text' : 'password';
  }

async function saveUser(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('modal-submit');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Saving...';
    submitBtn.disabled = true;
    
    const formData = {
        username: document.getElementById('modal-username').value.trim(),
        password: document.getElementById('modal-password').value
    };
    
    if (!formData.username) {
        alert('Username is required');
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
        return;
    }
    
    if (!editingUserId && !formData.password) {
        alert('Password is required for new users');
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
        return;
    }
    
    if (editingUserId) {
        formData.id = editingUserId;
    }
    
    const endpoint = editingUserId ? 'api/update_user.php' : 'api/create_user.php';
    
    try {
        const res = await fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        });
        
        const data = await res.json();
        
        if (!res.ok || !data.success) {
            throw new Error(data.message || 'Failed to save user');
        }
        
        alert(data.message);
        hideUserModal();
        await loadUsers();
        
    } catch (err) {
        console.error('Error saving user:', err);
        alert('Error: ' + err.message);
    } finally {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    }
}

  async function editUser(user) {
    showUserModal(user);
  }

  async function deleteUser(userId) {
    if (!confirm('⚠️ Are you sure you want to delete this user? This action cannot be undone.')) {
      return;
    }
    
    try {
      const res = await fetch('api/delete_user.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: userId })
      });
      
      const data = await res.json();
      
      if (!res.ok || !data.success) {
        throw new Error(data.message || 'Failed to delete user');
      }
      
      alert(data.message);
      await loadUsers();
      
    } catch (err) {
      console.error('Error deleting user:', err);
      alert('Error: ' + err.message);
    }
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

  document.addEventListener('DOMContentLoaded', function() {

    if (document.getElementById('pending-reports-list')) {
      loadPendingReports();
      document.getElementById('btn-refresh-pending').addEventListener('click', loadPendingReports);
    }
    

    if (document.getElementById('user-list')) {
      loadUsers();
      
 
      document.getElementById('btn-add-user').addEventListener('click', () => showUserModal());
      

      document.getElementById('btn-refresh-users').addEventListener('click', loadUsers);
      

      const searchInput = document.getElementById('user-search');
      if (searchInput) {
        searchInput.addEventListener('input', loadUsers);
      }
    }
    

    const btnApplyFilters = document.getElementById('btn-apply-filters');
    const btnClearFilters = document.getElementById('btn-clear-filters');
    
    if (btnApplyFilters) {
      btnApplyFilters.addEventListener('click', async () => {
        if (typeof window.loadDisasters === 'function') {
          await window.loadDisasters();
        }
      });
    }
    
    if (btnClearFilters) {
      btnClearFilters.addEventListener('click', async () => {
        document.getElementById('filter_type').value = '';
        document.getElementById('from').value = '';
        document.getElementById('to').value = '';
        document.getElementById('q').value = '';
        if (typeof window.loadDisasters === 'function') {
          await window.loadDisasters();
        }
      });
    }
    

    const modalCancel = document.getElementById('modal-cancel');
    const userForm = document.getElementById('user-form');
    const modal = document.getElementById('user-modal');
    const showPasswordCheckbox = document.getElementById('show-password');
    
    if (modalCancel) {
      modalCancel.addEventListener('click', hideUserModal);
    }
    
    if (userForm) {
      userForm.addEventListener('submit', saveUser);
    }
    
    if (modal) {
      modal.addEventListener('click', (e) => {
        if (e.target === modal) {
          hideUserModal();
        }
      });
    }
    
    if (showPasswordCheckbox) {
      showPasswordCheckbox.addEventListener('change', togglePassword);
    }
  });


  window.loadPendingReports = loadPendingReports;
  window.reviewReport = reviewReport;
  window.loadUsers = loadUsers;
  window.editUser = editUser;
  window.deleteUser = deleteUser;
  window.escapeHtml = escapeHtml;
  window.formatDateTime = formatDateTime;
  </script>
</body>
</html>
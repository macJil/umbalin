# Baguio City – Disaster Risk Response Map

A simple PHP + MySQL + Leaflet (OpenStreetMap) web app to record and visualize disaster reports in Baguio City. You can add events (flood, earthquake, typhoon, accident), store them in MySQL, and draw circle impact areas on a map.

## Features

- Leaflet map centered on Baguio City (uses free OpenStreetMap tiles)
 - Report disasters with: name, type (earthquake, typhoon, landslide, flood), date/time, intensity/signal, address, radius, notes
- Pick location by map click, drag, or geocoding an address
- Store in MySQL; list and filter by type/date/search
- Colored circles by type and info windows on the map

## Prerequisites

- XAMPP (Apache + MySQL) running on Windows
- Internet access to load Leaflet and OpenStreetMap tiles

## Setup

1. Move the project folder to your XAMPP htdocs (already in `c:\xampp\htdocs\project`).
2. Create the database and table:
   - Open phpMyAdmin at http://localhost/phpmyadmin
   - Create the database by importing `db.sql` (or paste it into the SQL tab)
3. Configure DB connection in `config.php` if needed (defaults assume XAMPP: user `root`, no password):
   - DB_NAME: `disaster_db`
   - DB_USER: `root`
   - DB_PASS: `` (empty)
4. Visit the app:
   - http://localhost/project/

### Geocoding notes

- The app uses OpenStreetMap Nominatim for geocoding addresses. It appends ", Baguio City" to your query for locality.
- For responsible usage, edit `assets/js/app.js` to replace the placeholder email parameter in the Nominatim URL (look for `email=you@example.com`).

## Usage

- Report a disaster in the left panel and click Save Report. The map will update and the event will appear in the list.
- Click on the map to set precise coordinates; adjust radius with the slider.
- Use the Filter panel to narrow down results by type, date range, or search text.

## Endpoints (for reference)

- `POST /project/api/create_disaster.php`
  - Body (JSON):
    ```json
      {
         "name": "Kennon Road Flooding",
         "type": "flood",
      "occurred_at": "2025-10-24T10:30",
      "intensity_signal": "Moderate",
      "latitude": 16.4023,
      "longitude": 120.5960,
      "radius_m": 250,
      "address": "Kennon Road, Baguio",
      "description": "Water level in low-lying areas"
    }
    ```
- `GET /project/api/list_disasters.php?type=&from=&to=&q=` returns `{ success, data: [] }`
- `POST /project/api/delete_disaster.php` with `{ "id": 123 }` to delete

## Notes

- All times are stored in server time (DATETIME). Ensure your PHP timezone is set as needed.
- The map starts centered on Baguio City. Geocoding always appends ", Baguio City" for local context.
- This is a minimal demo and does not include authentication or role-based access.

## Troubleshooting

- If the map doesn't load, check the browser console for errors and verify you have internet connectivity to load Leaflet and OSM tiles.
- If saving reports fails, check `config.php` DB credentials and confirm `db.sql` was imported.
- PHP errors can be found in Apache's error log (XAMPP control panel -> Apache -> Logs).

## Type colors

- Earthquake: red
- Typhoon: purple
- Landslide: brown
- Flood: pink

## Optional: Using Google Maps instead

If you prefer Google Maps (requires an API key), you can revert to the original approach:
- In `index.html`, include the Google Maps script and remove Leaflet CSS/JS.
- In `assets/js/app.js`, restore the Google-specific implementation (previous version).

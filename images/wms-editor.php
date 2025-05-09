<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export'])) {
    $layers = json_decode($_POST['layers'], true);
    
    // Debug logging
    error_log('Received layers data: ' . print_r($layers, true));
    
    if (!$layers || !is_array($layers)) {
        error_log('Invalid layers data received');
        die('Invalid layers data');
    }
    
    $storyContent = '';
    $exportType = $_POST['exportType'] ?? 'vertical';
    
    foreach ($layers as $index => $layer) {
        $storyContent .= sprintf(
            '<div class="story-section" data-place="Layer%d">%s</div>',
            $index,
            $layer['content']
        );
    }
    
    $layersJson = json_encode($layers);
    
    header('Content-Type: text/html');
    header('Content-Disposition: attachment; filename="wms-story.html"');
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WMS Story Map</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>
        html, body { 
            margin: 0; padding: 0; overflow: hidden; font-family: Arial, sans-serif;
        }
        #map {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            width: 100vw;
            height: 100vh;
            z-index: 1;
        }
        .custom-popup {
            position: absolute;
            background: white;
            border-radius: 4px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            padding: 10px;
            max-width: 300px;
            max-height: 400px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
        }
        .custom-popup .close-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background: none;
            border: none;
            font-size: 16px;
            cursor: pointer;
            padding: 0 5px;
        }
        .custom-popup .title {
            font-weight: bold;
            margin-bottom: 10px;
            padding-right: 20px;
        }
        .custom-popup .content {
            font-size: 14px;
        }
        <?php if ($exportType === 'vertical') { ?>
        .story {
            position: fixed;
            top: 40px;
            left: 24px;
            width: 500px;
            background: transparent;
            box-shadow: none;
            border-radius: 10px;
            padding: 32px;
            z-index: 10;
            overflow-y: auto;
            max-height: calc(100vh - 80px);
            scrollbar-width: thin;
            scrollbar-color: #444 transparent;
        }
        .story::-webkit-scrollbar {
            width: 12px;
            background: transparent;
        }
        .story::-webkit-scrollbar-track {
            background: transparent;
        }
        .story::-webkit-scrollbar-thumb {
            background: #444;
            border-radius: 8px;
            border: 3px solid transparent;
            background-clip: content-box;
        }
        .story::-webkit-scrollbar-button {
            display: none;
            height: 0;
            width: 0;
        }
        .story-section { 
            min-height: calc(100vh - 50px);
            padding: 20px;
            margin-bottom: 20px;
            background: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .story-section:last-child {
            margin-bottom: 0;
        }
        /* Popup content fixes */
        .leaflet-popup-content {
            max-width: 420px;
            max-height: 300px;
            overflow: auto;
        }
        .leaflet-popup-content table {
            display: block;
            width: 100%;
            overflow-x: auto;
            word-break: break-word;
        }
        .leaflet-popup-content td, .leaflet-popup-content th {
            white-space: nowrap;
        }
        .feature-info-panel {
            position: fixed;
            top: 40px;
            right: 24px;
            width: 500px;
            background: rgba(255,255,255,0.92);
            box-shadow: 0 4px 24px rgba(0,0,0,0.18);
            border-radius: 10px;
            padding: 32px;
            z-index: 10000;
            max-height: calc(100vh - 80px);
            overflow-y: auto;
            display: none;
        }
        .feature-info-panel .close-btn {
            position: absolute;
            top: 16px;
            right: 24px;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #888;
            cursor: pointer;
        }
        .feature-info-panel h4 {
            margin-top: 0;
        }
        #map {
            left: 0;
            width: 100vw;
        }
        <?php } else if ($exportType === 'horizontal') { ?>
        html, body {
            background: transparent !important;
            height: auto !important;
            min-height: 0 !important;
        }
        .story-container {
            position: fixed;
            top: 40px;
            left: 24px;
            width: 500px;
            background: transparent;
            z-index: 10;
            padding-bottom: 16px;
        }
        .story-section {
            min-height: 0;
            padding: 20px;
            background: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            max-height: 66vh;
            margin-top: 0;
            margin-bottom: 40px;
            overflow-y: auto;
        }
        .navigation {
            position: fixed;
            bottom: 40px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 20;
            display: flex;
            gap: 20px;
            background: rgba(255, 255, 255, 0.9);
            padding: 15px 30px;
            border-radius: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .nav-button {
            padding: 10px 20px;
            border: none;
            border-radius: 20px;
            background: #4CAF50;
            color: white;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
        }
        .nav-button:hover {
            background: #45a049;
        }
        .nav-button:disabled {
            background: #cccccc;
            cursor: not-allowed;
        }
        .progress-bar {
            position: fixed;
            top: 0;
            left: 0;
            height: 4px;
            background: #4CAF50;
            transition: width 0.3s ease;
            z-index: 30;
        }
        .feature-info-panel {
            position: fixed;
            top: 40px;
            right: 24px;
            width: 500px;
            background: rgba(255,255,255,0.92);
            box-shadow: 0 4px 24px rgba(0,0,0,0.18);
            border-radius: 10px;
            padding: 32px;
            z-index: 20;
            max-height: calc(100vh - 80px);
            overflow-y: auto;
            display: none;
        }
        .feature-info-panel .close-btn {
            position: absolute;
            top: 16px;
            right: 24px;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #888;
            cursor: pointer;
        }
        .feature-info-panel h4 {
            margin-top: 0;
        }
        #map {
            left: 0;
            width: 100vw;
        }
        <?php } ?>
    </style>
</head>
<body>
    <?php if ($exportType === 'horizontal') { ?>
    <div class="progress-bar" id="progressBar"></div>
    <?php } ?>
    <div id="map"></div>
    <div id="customPopup" class="custom-popup">
        <button class="close-btn">&times;</button>
        <div class="title"></div>
        <div class="content"></div>
    </div>
    <?php if ($exportType === 'vertical') { ?>
    <div class="story">
        <?php echo $storyContent; ?>
    </div>
    <?php } else if ($exportType === 'horizontal') { ?>
    <div class="story-container">
        <div class="story-section" id="currentStorySection"></div>
    </div>
    <div class="navigation">
        <button class="nav-button" id="prevButton" disabled>Previous</button>
        <button class="nav-button" id="nextButton">Next</button>
    </div>
    <?php } ?>
    <div class="feature-info-panel" id="featureInfoPanel">
        <button class="close-btn" id="closeFeatureInfo">&times;</button>
        <div id="featureInfoContent"></div>
    </div>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mapContainer = document.getElementById('map');
            if (!mapContainer) {
                console.error('Map container not found');
                return;
            }

            const map = L.map('map', { zoomControl: false }).setView([0, 0], 2);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            const layers = <?php echo $layersJson; ?>;
            let currentWmsLayer = null;
            let currentEditor = null;
            let currentLayerIndex = -1;
            const customPopup = document.getElementById('customPopup');
            const closePopupBtn = customPopup.querySelector('.close-btn');

            // Close popup when clicking the close button
            closePopupBtn.onclick = function() {
                customPopup.style.display = 'none';
            };

            // Close popup when clicking outside
            map.on('click', function(e) {
                // Only close if we're not clicking on the popup itself
                const popup = document.getElementById('customPopup');
                if (popup && !popup.contains(e.originalEvent.target)) {
                    popup.style.display = 'none';
                }
            });

            function showCustomPopup(latlng, title, content) {
                const point = map.latLngToContainerPoint(latlng);
                const popup = document.getElementById('customPopup');
                popup.querySelector('.title').textContent = title;
                popup.querySelector('.content').innerHTML = content;
                popup.style.left = (point.x + 10) + 'px';
                popup.style.top = (point.y + 10) + 'px';
                popup.style.display = 'block';
            }

            <?php if ($exportType === 'vertical') { ?>
            let activeSection = 0;

            function onScroll() {
                let closest = null;
                let minDistance = Infinity;

                document.querySelectorAll('.story-section').forEach((section, index) => {
                    const rect = section.getBoundingClientRect();
                    const distance = Math.abs(rect.top);
                    if (distance < minDistance) {
                        minDistance = distance;
                        closest = index;
                    }
                });

                if (closest !== null && closest !== activeSection) {
                    activeSection = closest;
                    showLayer(layers[closest]);
                }
            }

            document.querySelector('.story').addEventListener('scroll', onScroll);
            window.addEventListener('load', onScroll);
            // Show the first layer on load
            onScroll();
            showLayer(layers[0]);
            <?php } else { ?>
            let currentIndex = 0;
            const storySection = document.getElementById('currentStorySection');
            const prevButton = document.getElementById('prevButton');
            const nextButton = document.getElementById('nextButton');
            const progressBar = document.getElementById('progressBar');

            function updateNavigation() {
                prevButton.disabled = currentIndex === 0;
                nextButton.disabled = currentIndex === layers.length - 1;
                const progress = ((currentIndex + 1) / layers.length) * 100;
                progressBar.style.width = `${progress}%`;
            }

            function updateStorySection() {
                storySection.innerHTML = layers[currentIndex].content;
            }

            function updateStoryPosition() {
                updateStorySection();
                showLayer(layers[currentIndex]);
                updateNavigation();
            }

            prevButton.addEventListener('click', () => {
                if (currentIndex > 0) {
                    currentIndex--;
                    updateStoryPosition();
                }
            });

            nextButton.addEventListener('click', () => {
                if (currentIndex < layers.length - 1) {
                    currentIndex++;
                    updateStoryPosition();
                }
            });

            // Initialize
            updateStoryPosition();
            <?php } ?>

            function showLayer(layer) {
                if (currentWmsLayer) {
                    map.removeLayer(currentWmsLayer);
                }

                let wmsUrl = (layer.url || '').replace(/^@+/, '').trim();
                let layerName = layer.name;

                try {
                    let wmsLayer = L.tileLayer.wms(wmsUrl, {
                        layers: layerName,
                        format: 'image/png',
                        transparent: true,
                        identify: true  // Enable identify for popups
                    });
                    currentWmsLayer = wmsLayer;
                    wmsLayer.addTo(map);

                    const boundsArr = [[layer.bounds.south, layer.bounds.west], [layer.bounds.north, layer.bounds.east]];
                    map.fitBounds(boundsArr);
                    
                    // Pan the map to the right by the width of the panel (panel: 500px + margin: 24px)
                    setTimeout(function() {
                        map.panBy([-524, 0], {animate: false});
                        map.setZoom(map.getZoom() - 1); // Zoom out for more context
                    }, 300);
                } catch (e) {
                    console.error('Failed to add WMS layer:', e);
                    alert('Failed to load WMS layer: ' + layerName);
                }
            }

            // Feature info panel functionality
            const featureInfoPanel = document.getElementById('featureInfoPanel');
            const closeFeatureInfo = document.getElementById('closeFeatureInfo');

            closeFeatureInfo.onclick = function() {
                featureInfoPanel.style.display = 'none';
            };

            map.on('click', function(e) {
                if (!currentWmsLayer) return;

                const point = map.latLngToContainerPoint(e.latlng, map.getZoom());
                const size = map.getSize();
                const bounds = map.getBounds();
                const bbox = `${bounds.getWest()},${bounds.getSouth()},${bounds.getEast()},${bounds.getNorth()}`;
                <?php if ($exportType === 'vertical') { ?>
                const layer = layers[activeSection ?? 0];
                const safeLayer = (Array.isArray(layers) && layers.length && typeof activeSection === 'number' && layers[activeSection]) ? layers[activeSection] : layers[0];
                <?php } else { ?>
                const layer = layers[currentIndex];
                const safeLayer = (Array.isArray(layers) && layers.length && typeof currentIndex === 'number' && layers[currentIndex]) ? layers[currentIndex] : layers[0];
                <?php } ?>
                const params = {
                    request: 'GetFeatureInfo',
                    service: 'WMS',
                    srs: 'EPSG:4326',
                    styles: '',
                    transparent: true,
                    version: '1.1.1',
                    format: 'image/png',
                    bbox: bbox,
                    height: size.y,
                    width: size.x,
                    layers: safeLayer.name,
                    query_layers: safeLayer.name,
                    info_format: 'text/html',
                    x: Math.floor(point.x),
                    y: Math.floor(point.y),
                    feature_count: 10,
                    exceptions: 'XML'
                };

                let baseUrl = safeLayer.url.replace(/^@+/, '').trim();
                if (baseUrl.endsWith('?') || baseUrl.endsWith('&')) {
                    baseUrl = baseUrl.slice(0, -1);
                }
                const url = baseUrl + (baseUrl.includes('?') ? '&' : '?') + new URLSearchParams(params).toString();
                
                console.log('GetFeatureInfo request URL:', url);
                console.log('Layer name:', safeLayer.name);
                console.log('Click coordinates:', e.latlng);
                console.log('Map bounds:', bounds);
                console.log('Point coordinates:', point);

                fetch(url)
                    .then(response => {
                        console.log('GetFeatureInfo response status:', response.status);
                        console.log('GetFeatureInfo response headers:', Object.fromEntries(response.headers.entries()));
                        return response.text();
                    })
                    .then(html => {
                        console.log('GetFeatureInfo response:', html);
                        if (html && html.trim() !== '') {
                            let output = '';
                            try {
                                const parser = new DOMParser();
                                const doc = parser.parseFromString(html, 'text/html');
                                
                                // Remove any style tags
                                const styleTags = doc.getElementsByTagName('style');
                                while (styleTags.length > 0) {
                                    styleTags[0].parentNode.removeChild(styleTags[0]);
                                }

                                // Get the table content
                                const tables = doc.querySelectorAll('table');
                                if (tables.length > 0) {
                                    tables.forEach(table => {
                                        const rows = table.querySelectorAll('tr');
                                        rows.forEach(row => {
                                            const cells = row.querySelectorAll('td,th');
                                            if (cells.length === 2) {
                                                const label = cells[0].textContent.trim();
                                                const value = cells[1].textContent.trim();
                                                if (label && value) {
                                                    output += `<div><strong>${label}:</strong> ${value}</div>`;
                                                }
                                            } else if (cells.length === 1) {
                                                const content = cells[0].textContent.trim();
                                                if (content) {
                                                    output += `<div>${content}</div>`;
                                                }
                                            }
                                        });
                                    });
                                }

                                // If no table content, try to get any meaningful content
                                if (!output) {
                                    const bodyContent = doc.body.textContent.trim();
                                    if (bodyContent) {
                                        // Remove any CSS-like content
                                        output = bodyContent.replace(/[{}\[\]()]/g, '')
                                            .replace(/[a-zA-Z-]+:/g, '')
                                            .replace(/\s+/g, ' ')
                                            .trim();
                                    }
                                }

                                // If still no output, check for XML error
                                if (!output) {
                                    const xmlDoc = parser.parseFromString(html, 'text/xml');
                                    const error = xmlDoc.querySelector('ServiceException');
                                    if (error) {
                                        output = `Error: ${error.textContent.trim()}`;
                                    }
                                }
                            } catch (e) {
                                console.error('Error parsing feature info:', e);
                                output = 'Error parsing feature information';
                            }

                            // Always update and show the custom panel for both vertical and horizontal modes
                            if (output && output !== '') {
                                document.getElementById('featureInfoContent').innerHTML = output;
                                featureInfoPanel.style.display = 'block';
                            } else {
                                document.getElementById('featureInfoContent').innerHTML = '<em>No feature info returned. Try zooming in closer to features.</em>';
                                featureInfoPanel.style.display = 'block';
                            }
                        } else {
                            document.getElementById('featureInfoContent').innerHTML = '<em>No feature info returned. Try zooming in closer to features.</em>';
                            featureInfoPanel.style.display = 'block';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching feature info:', error);
                        document.getElementById('featureInfoContent').innerHTML = '<em>Error fetching feature info: ' + error + '</em>';
                        featureInfoPanel.style.display = 'block';
                    });
            });

            // Ensure .feature-info-panel is visible in vertical mode
            const featureInfoPanelEl = document.getElementById('featureInfoPanel');
            if (featureInfoPanelEl) {
                featureInfoPanelEl.style.display = 'block';
                featureInfoPanelEl.style.zIndex = 10000;
            }
        });
    </script>
</body>
</html>
<?php
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WMS Story Map Editor</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <!-- Quill CSS -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        #map {
            height: 400px;
            width: 100%;
            margin-bottom: 20px;
        }
        .story-content {
            min-height: 200px;
            margin-bottom: 20px;
        }
        .preview-map {
            height: 300px;
            width: 100%;
        }
        .layer-editor {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .ql-container {
            height: 200px;
        }
        .wizard-step {
            display: none;
        }
        .wizard-step.active {
            display: block;
        }
        .wizard-nav {
            margin-bottom: 20px;
        }
        .wizard-nav .btn {
            margin-right: 10px;
        }
        .layer-list {
            margin-bottom: 20px;
        }
        .layer-item {
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: #f8f9fa;
        }
        .layer-item.active {
            border-color: #0d6efd;
            background: #e7f1ff;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4">WMS Story Map Editor</h1>
        
        <!-- Wizard Navigation -->
        <div class="wizard-nav">
            <button class="btn btn-primary" id="addNewLayer">Add New Layer</button>
            <button class="btn btn-success" id="exportBtn">Export Story Map</button>
        </div>

        <!-- Layer List -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Layers</h5>
            </div>
            <div class="card-body">
                <div id="layerList" class="layer-list"></div>
            </div>
        </div>

        <!-- Wizard Steps Container -->
        <div id="wizardSteps">
            <!-- Layer Configuration Step -->
            <div class="wizard-step" id="layerConfigStep">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Configure WMS Layer</h5>
                    </div>
                    <div class="card-body">
                        <form id="wmsForm" onsubmit="return false;">
                            <div class="mb-3">
                                <label for="wmsUrl" class="form-label">WMS URL</label>
                                <input type="url" class="form-control" id="wmsUrl" required>
                            </div>
                            <div class="mb-3">
                                <label for="layerName" class="form-label">Layer Name (workspace:layer)</label>
                                <input type="text" class="form-control" id="layerName" required>
                            </div>
                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-secondary" id="cancelLayer">Cancel</button>
                                <button type="button" class="btn btn-primary" id="addLayer">Add Layer</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Layer Content Step -->
            <div class="wizard-step" id="layerContentStep">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Edit Layer Content</h5>
                    </div>
                    <div class="card-body">
                        <div id="currentLayerEditor"></div>
                        <div class="mt-3">
                            <button type="button" class="btn btn-primary" id="saveContent">Save Content</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preview -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Preview</h5>
            </div>
            <div class="card-body">
                <div id="previewMap" class="preview-map"></div>
                <div id="previewContent" class="mt-3"></div>
            </div>
        </div>

        <!-- Export Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Export Options</h5>
            </div>
            <div class="card-body">
                <form method="post" id="exportForm">
                    <input type="hidden" name="layers" id="layersData">
                    <input type="hidden" name="export" value="1">
                    <div class="mb-3">
                        <label class="form-label">Export Format:</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" id="vertical" name="exportType" value="vertical" checked>
                            <label class="form-check-label" for="vertical">Vertical Scrolling</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" id="horizontal" name="exportType" value="horizontal">
                            <label class="form-check-label" for="horizontal">Horizontal Navigation</label>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const previewMapContainer = document.getElementById('previewMap');
            
            if (!previewMapContainer) {
                console.error('Preview map container not found');
                return;
            }

            // Initialize preview map
            const previewMap = L.map('previewMap', { zoomControl: false }).setView([0, 0], 2);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(previewMap);

            let layers = [];
            let currentWmsLayer = null;
            let currentEditor = null;
            let currentLayerIndex = -1;

            // Quill editor configuration
            const quillOptions = {
                theme: 'snow',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline', 'strike'],
                        ['blockquote', 'code-block'],
                        [{ 'header': 1 }, { 'header': 2 }],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        [{ 'script': 'sub'}, { 'script': 'super' }],
                        [{ 'indent': '-1'}, { 'indent': '+1' }],
                        [{ 'direction': 'rtl' }],
                        [{ 'size': ['small', false, 'large', 'huge'] }],
                        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                        [{ 'color': [] }, { 'background': [] }],
                        [{ 'font': [] }],
                        [{ 'align': [] }],
                        ['clean']
                    ]
                }
            };

            function showStep(stepId) {
                document.querySelectorAll('.wizard-step').forEach(step => {
                    step.classList.remove('active');
                });
                document.getElementById(stepId).classList.add('active');
            }

            function updateLayerList() {
                const container = document.getElementById('layerList');
                container.innerHTML = '';
                
                layers.forEach((layer, index) => {
                    const layerDiv = document.createElement('div');
                    layerDiv.className = `layer-item ${index === currentLayerIndex ? 'active' : ''}`;
                    layerDiv.innerHTML = `
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${layer.name}</strong>
                                <div class="text-muted small">${layer.url}</div>
                            </div>
                            <div>
                                <button class="btn btn-sm btn-primary me-2" onclick="editLayer(${index})">Edit</button>
                                <button class="btn btn-sm btn-danger" onclick="removeLayer(${index})">Remove</button>
                            </div>
                        </div>
                    `;
                    container.appendChild(layerDiv);
                });
            }

            // Add New Layer Button
            document.getElementById('addNewLayer').addEventListener('click', () => {
                document.getElementById('wmsForm').reset();
                showStep('layerConfigStep');
            });

            // Cancel Layer Button
            document.getElementById('cancelLayer').addEventListener('click', () => {
                showStep('layerContentStep');
            });

            // Add Layer Button
            document.getElementById('addLayer').addEventListener('click', async () => {
                const wmsUrl = document.getElementById('wmsUrl').value;
                const layerName = document.getElementById('layerName').value;

                try {
                    const response = await fetch(`${wmsUrl}?service=WMS&version=1.3.0&request=GetCapabilities`);
                    const text = await response.text();
                    const parser = new DOMParser();
                    const xml = parser.parseFromString(text, "application/xml");

                    const layerNode = Array.from(xml.querySelectorAll("Layer > Layer"))
                        .find(l => l.querySelector("Name")?.textContent === layerName);

                    if (!layerNode) {
                        alert('Layer not found in WMS service');
                        return;
                    }

                    const bboxNode = layerNode.querySelector("EX_GeographicBoundingBox");
                    if (!bboxNode) {
                        alert('Layer bounds not found');
                        return;
                    }

                    const layer = {
                        url: wmsUrl,
                        name: layerName,
                        bounds: {
                            west: parseFloat(bboxNode.querySelector("westBoundLongitude").textContent),
                            east: parseFloat(bboxNode.querySelector("eastBoundLongitude").textContent),
                            south: parseFloat(bboxNode.querySelector("southBoundLatitude").textContent),
                            north: parseFloat(bboxNode.querySelector("northBoundLatitude").textContent)
                        },
                        content: ''
                    };

                    layers.push(layer);
                    currentLayerIndex = layers.length - 1;
                    updateLayerList();
                    addLayerToMap(layer);
                    showStep('layerContentStep');
                    initializeEditor();
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error fetching WMS capabilities');
                }
            });

            // Save Content Button
            document.getElementById('saveContent').addEventListener('click', () => {
                if (currentLayerIndex >= 0 && currentEditor) {
                    layers[currentLayerIndex].content = currentEditor.root.innerHTML;
                    updateLayerList();
                }
            });

            window.editLayer = function(index) {
                currentLayerIndex = index;
                updateLayerList();
                showStep('layerContentStep');
                initializeEditor();
            };

            window.removeLayer = function(index) {
                layers.splice(index, 1);
                if (currentLayerIndex === index) {
                    currentLayerIndex = -1;
                    currentEditor = null;
                } else if (currentLayerIndex > index) {
                    currentLayerIndex--;
                }
                updateLayerList();
                updateMap();
            };

            function initializeEditor() {
                const container = document.getElementById('currentLayerEditor');
                container.innerHTML = '<div id="editor"></div>';
                
                currentEditor = new Quill('#editor', quillOptions);
                if (currentLayerIndex >= 0) {
                    currentEditor.root.innerHTML = layers[currentLayerIndex].content || '';
                }
            }

            function addLayerToMap(layer) {
                const wmsLayer = L.tileLayer.wms(layer.url, {
                    layers: layer.name,
                    format: 'image/png',
                    transparent: true,
                    identify: true  // Enable identify for popups
                });

                if (currentWmsLayer) {
                    previewMap.removeLayer(currentWmsLayer);
                }
                currentWmsLayer = wmsLayer;
                wmsLayer.addTo(previewMap);

                // Remove previous click handlers before adding a new one
                previewMap.off('click');

                previewMap.on('click', function(e) {
                    const point = previewMap.latLngToContainerPoint(e.latlng, previewMap.getZoom());
                    const size = previewMap.getSize();
                    const bounds = previewMap.getBounds();
                    const crs = 'EPSG:4326';
                    const bbox = `${bounds.getSouth()},${bounds.getWest()},${bounds.getNorth()},${bounds.getEast()}`;
                    const params = {
                        REQUEST: 'GetFeatureInfo',
                        SERVICE: 'WMS',
                        CRS: crs,
                        STYLES: '',
                        TRANSPARENT: true,
                        VERSION: '1.3.0',
                        FORMAT: 'image/png',
                        BBOX: bbox,
                        HEIGHT: size.y,
                        WIDTH: size.x,
                        LAYERS: layer.name,
                        QUERY_LAYERS: layer.name,
                        INFO_FORMAT: 'text/html',
                        I: Math.round(point.x),
                        J: Math.round(point.y),
                        FEATURE_COUNT: 10
                    };
                    // Fix: Ensure only a single '?' in the URL
                    let baseUrl = layer.url;
                    if (baseUrl.endsWith('?') || baseUrl.endsWith('&')) {
                        baseUrl = baseUrl.slice(0, -1);
                    }
                    const url = baseUrl + (baseUrl.includes('?') ? '&' : '?') + new URLSearchParams(params).toString();
                    console.log('Fetching GetFeatureInfo from:', url);
                    fetch(url)
                        .then(response => response.text())
                        .then(html => {
                            if (html && html.trim() !== '') {
                                // Debug log
                                console.log('Setting feature info:', html);
                                // Only show the custom panel, not the Leaflet popup
                                document.getElementById('featureInfoContent').innerHTML = html;
                                featureInfoPanel.style.display = 'block';
                            }
                        })
                        .catch(error => console.error('Error fetching feature info:', error));
                });
                var closeBtn = document.getElementById('closeFeatureInfo');
                if (closeBtn) {
                    closeBtn.onclick = function() {
                        document.getElementById('featureInfoPanel').style.display = 'none';
                    };
                }

                const boundsArr = [[layer.bounds.south, layer.bounds.west], [layer.bounds.north, layer.bounds.east]];
                previewMap.fitBounds(boundsArr);
                // Pan the preview map to the right by the width of the panel (panel: 500px + margin: 24px)
                setTimeout(function() {
                    previewMap.panBy([-524, 0], {animate: false});
                    previewMap.setZoom(previewMap.getZoom() - 1);
                }, 300);
            }

            function updateMap() {
                if (layers.length > 0) {
                    addLayerToMap(layers[layers.length - 1]);
                } else {
                    if (currentWmsLayer) {
                        previewMap.removeLayer(currentWmsLayer);
                        currentWmsLayer = null;
                    }
                    previewMap.setView([0, 0], 2);
                }
            }

            // Export Button
            document.getElementById('exportBtn').addEventListener('click', () => {
                const form = document.getElementById('exportForm');
                document.getElementById('layersData').value = JSON.stringify(layers);
                form.submit();
            });

            // Initialize the first step
            showStep('layerContentStep');
        });
    </script>
</body>
</html> 
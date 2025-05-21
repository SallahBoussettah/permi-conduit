<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laravel Upload Test</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #333;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-top: 0;
        }
        .settings-container {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="file"] {
            display: block;
            width: 100%;
            padding: 8px 0;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #45a049;
        }
        .results {
            margin-top: 20px;
            display: none;
        }
        .results.visible {
            display: block;
        }
        .results pre {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            max-height: 500px;
            overflow-y: auto;
            border: 1px solid #ddd;
        }
        .status {
            margin: 10px 0;
            padding: 10px;
            border-radius: 4px;
        }
        .status.success {
            background-color: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }
        .status.error {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
        .debug-info {
            margin-top: 20px;
            display: none;
        }
        .debug-info.visible {
            display: block;
        }
        .toggle-debug {
            background-color: #607d8b;
            margin-top: 10px;
        }
        .spinner {
            display: none;
            margin-left: 10px;
            vertical-align: middle;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .progress-container {
            margin-top: 15px;
            display: none;
        }
        progress {
            width: 100%;
            height: 20px;
        }
        .file-details {
            margin-top: 10px;
            display: none;
        }
        .file-details.visible {
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Laravel Upload Test</h1>
        
        <div class="settings-container">
            <h3>Current Settings</h3>
            <p><strong>PHP upload_max_filesize:</strong> {{ ini_get('upload_max_filesize') }}</p>
            <p><strong>PHP post_max_size:</strong> {{ ini_get('post_max_size') }}</p>
            <p><strong>Laravel max_upload_size:</strong> {{ config('filesystems.max_upload_size') ?? 'Not Set' }}</p>
        </div>

        <form id="uploadForm" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="MAX_FILE_SIZE" value="41943040" />
            <div class="form-group">
                <label for="testFile">Select an audio file to upload (max 40MB):</label>
                <input type="file" id="testFile" name="test_file" accept="audio/*" />
                <div class="file-details" id="fileDetails">
                    <p><strong>File Name:</strong> <span id="fileName"></span></p>
                    <p><strong>File Size:</strong> <span id="fileSize"></span></p>
                    <p><strong>File Type:</strong> <span id="fileType"></span></p>
                </div>
            </div>

            <button type="submit" id="uploadButton">Upload Audio File</button>
            <span class="spinner" id="spinner"></span>
            
            <div class="progress-container" id="progressContainer">
                <p>Uploading... <span id="progressPercent">0%</span></p>
                <progress id="progressBar" value="0" max="100"></progress>
            </div>
        </form>

        <div class="results" id="results">
            <h3>Upload Results</h3>
            <div class="status" id="statusBox"></div>
            <button id="toggleDebug" class="toggle-debug">Show Technical Details</button>
            <div class="debug-info" id="debugInfo">
                <pre id="rawData"></pre>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const uploadForm = document.getElementById('uploadForm');
            const fileInput = document.getElementById('testFile');
            const fileDetails = document.getElementById('fileDetails');
            const fileName = document.getElementById('fileName');
            const fileSize = document.getElementById('fileSize');
            const fileType = document.getElementById('fileType');
            const resultsDiv = document.getElementById('results');
            const statusBox = document.getElementById('statusBox');
            const rawData = document.getElementById('rawData');
            const toggleDebug = document.getElementById('toggleDebug');
            const debugInfo = document.getElementById('debugInfo');
            const spinner = document.getElementById('spinner');
            const progressContainer = document.getElementById('progressContainer');
            const progressBar = document.getElementById('progressBar');
            const progressPercent = document.getElementById('progressPercent');
            const uploadButton = document.getElementById('uploadButton');

            // Handle file selection
            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const file = this.files[0];
                    
                    // Show file details
                    fileName.textContent = file.name;
                    fileSize.textContent = formatFileSize(file.size);
                    fileType.textContent = file.type;
                    fileDetails.classList.add('visible');

                    // Validate file size
                    const maxSize = 40 * 1024 * 1024; // 40MB
                    if (file.size > maxSize) {
                        statusBox.innerHTML = `<strong>Warning:</strong> File size (${formatFileSize(file.size)}) exceeds the 40MB limit. Upload may fail.`;
                        statusBox.className = 'status error';
                        resultsDiv.classList.add('visible');
                    } else {
                        resultsDiv.classList.remove('visible');
                    }
                } else {
                    fileDetails.classList.remove('visible');
                }
            });

            // Toggle debug info
            toggleDebug.addEventListener('click', function() {
                debugInfo.classList.toggle('visible');
                this.textContent = debugInfo.classList.contains('visible') ? 'Hide Technical Details' : 'Show Technical Details';
            });

            // Handle form submission
            uploadForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (!fileInput.files || !fileInput.files[0]) {
                    statusBox.innerHTML = '<strong>Error:</strong> Please select a file to upload.';
                    statusBox.className = 'status error';
                    resultsDiv.classList.add('visible');
                    return;
                }

                // Show loading indicators
                spinner.style.display = 'inline-block';
                progressContainer.style.display = 'block';
                uploadButton.disabled = true;
                
                const formData = new FormData(uploadForm);
                const xhr = new XMLHttpRequest();
                
                // Track upload progress
                xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        const percentComplete = Math.round((e.loaded / e.total) * 100);
                        progressBar.value = percentComplete;
                        progressPercent.textContent = percentComplete + '%';
                    }
                });

                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4) {
                        // Hide loading indicators
                        spinner.style.display = 'none';
                        uploadButton.disabled = false;
                        
                        if (xhr.status >= 200 && xhr.status < 300) {
                            try {
                                const response = JSON.parse(xhr.responseText);
                                
                                // Display result
                                if (response.success) {
                                    statusBox.innerHTML = `<strong>Success!</strong> ${response.message}`;
                                    statusBox.className = 'status success';
                                } else {
                                    statusBox.innerHTML = `<strong>Error:</strong> ${response.message}`;
                                    statusBox.className = 'status error';
                                }
                                
                                // Show raw data
                                rawData.textContent = JSON.stringify(response, null, 4);
                                resultsDiv.classList.add('visible');
                                
                            } catch (error) {
                                statusBox.innerHTML = `<strong>Error:</strong> Could not parse server response.`;
                                statusBox.className = 'status error';
                                rawData.textContent = xhr.responseText;
                                resultsDiv.classList.add('visible');
                            }
                        } else {
                            statusBox.innerHTML = `<strong>Server Error (${xhr.status}):</strong> ${xhr.statusText}`;
                            statusBox.className = 'status error';
                            rawData.textContent = xhr.responseText;
                            resultsDiv.classList.add('visible');
                        }
                    }
                };

                xhr.open('POST', '/laravel-upload-test', true);
                xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                xhr.send(formData);
            });

            // Format file size in human-readable format
            function formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                
                const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
                const i = Math.floor(Math.log(bytes) / Math.log(1024));
                
                return (bytes / Math.pow(1024, i)).toFixed(2) + ' ' + sizes[i];
            }
        });
    </script>
</body>
</html> 
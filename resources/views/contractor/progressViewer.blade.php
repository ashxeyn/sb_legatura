<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Viewer - {{ $documentName }}</title>
    <link rel="stylesheet"
        href="https://cdn-uicons.flaticon.com/2.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f3f4f6;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .viewer-header {
            background-color: #ffffff;
            padding: 16px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #e5e7eb;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            z-index: 10;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .document-icon {
            color: #3b82f6;
            font-size: 20px;
        }

        .document-title {
            color: #111827;
            font-size: 16px;
            font-weight: 600;
        }

        .download-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            background-color: #3b82f6;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: background-color 0.2s;
        }

        .download-btn:hover {
            background-color: #2563eb;
        }

        .viewer-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: auto;
            position: relative;
        }

        .document-image {
            max-width: 95%;
            max-height: 95%;
            object-fit: contain;
            background: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .document-iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        .unsupported-format {
            text-align: center;
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            max-width: 400px;
        }

        .unsupported-icon {
            font-size: 48px;
            color: #9ca3af;
            margin-bottom: 16px;
        }

        .unsupported-title {
            font-size: 18px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 8px;
        }

        .unsupported-desc {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 24px;
        }
    </style>
</head>

<body>
    <div class="viewer-header">
        <div class="header-left">
            <i class="fi fi-rr-document document-icon"></i>
            <span class="document-title">{{ $documentName }}</span>
        </div>
        <a href="{{ $documentUrl }}" download class="download-btn">
            <i class="fi fi-rr-download"></i>
            Download
        </a>
    </div>

    <div class="viewer-content">
        @if(in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
            <img src="{{ $documentUrl }}" alt="{{ $documentName }}" class="document-image">
        @else
            <div class="unsupported-format">
                <i class="fi fi-rr-file-word unsupported-icon"></i>
                <h3 class="unsupported-title">File must be downloaded</h3>
                <p class="unsupported-desc">This document format (.{{ $extension }}) cannot be viewed directly in the
                    browser.</p>
                <a href="{{ $documentUrl }}" download class="download-btn" style="justify-content: center;">
                    <i class="fi fi-rr-download"></i>
                    Download File
                </a>
            </div>
        @endif
    </div>
</body>

</html>
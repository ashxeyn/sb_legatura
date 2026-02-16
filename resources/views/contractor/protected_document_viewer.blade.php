<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Protected Document - {{ $documentType }}</title>
    <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/2.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #1a1a1a;
            overflow: hidden;
            position: relative;
            width: 100vw;
            height: 100vh;
        }

        .viewer-container {
            position: relative;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .viewer-header {
            background-color: rgba(0, 0, 0, 0.9);
            padding: 16px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            z-index: 100;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .lock-icon {
            color: #ec7e00;
            font-size: 20px;
        }

        .document-title {
            color: #ffffff;
            font-size: 16px;
            font-weight: 600;
        }

        .view-only-badge {
            background-color: rgba(236, 126, 0, 0.15);
            color: #ec7e00;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .document-wrapper {
            position: relative;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .document-image {
            max-width: 90%;
            max-height: 90%;
            object-fit: contain;
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            pointer-events: none;
        }

        .watermark-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('/img/legatura_watermark.png');
            background-size: 40%;
            background-position: center;
            background-repeat: repeat;
            opacity: 0.25;
            pointer-events: none;
            z-index: 10;
        }

        .protection-notice {
            position: absolute;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%);
            background-color: rgba(0, 0, 0, 0.8);
            color: rgba(255, 255, 255, 0.7);
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
            z-index: 100;
        }

        /* Disable right-click and text selection */
        body {
            -webkit-touch-callout: none;
            -webkit-user-select: none;
            -khtml-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }

        /* Prevent drag */
        img {
            -webkit-user-drag: none;
            -khtml-user-drag: none;
            -moz-user-drag: none;
            -o-user-drag: none;
            user-drag: none;
        }
    </style>
</head>
<body>
    <div class="viewer-container">
        <!-- Header -->
        <div class="viewer-header">
            <div class="header-left">
                <i class="fi fi-rr-lock lock-icon"></i>
                <span class="document-title">{{ $documentType }}</span>
            </div>
            <div class="view-only-badge">
                <i class="fi fi-rr-shield-check"></i>
                <span>View Only - Protected</span>
            </div>
        </div>

        <!-- Document Display -->
        <div class="document-wrapper">
            <img src="{{ $documentUrl }}" alt="{{ $documentType }}" class="document-image">
            <div class="watermark-overlay"></div>
        </div>

        <!-- Protection Notice -->
        <div class="protection-notice">
            <i class="fi fi-rr-info"></i>
            <span>This document is protected and for viewing purposes only</span>
        </div>
    </div>

    <script>
        // Disable right-click
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            return false;
        });

        // Disable keyboard shortcuts for saving/copying
        document.addEventListener('keydown', function(e) {
            // Prevent Ctrl+S, Ctrl+P, Ctrl+C, etc.
            if ((e.ctrlKey || e.metaKey) && (
                e.key === 's' || 
                e.key === 'p' || 
                e.key === 'c' ||
                e.key === 'a' ||
                e.key === 'u'
            )) {
                e.preventDefault();
                return false;
            }
            
            // Prevent F12, Ctrl+Shift+I (DevTools)
            if (e.key === 'F12' || 
                ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'I')) {
                e.preventDefault();
                return false;
            }
        });

        // Disable drag
        document.addEventListener('dragstart', function(e) {
            e.preventDefault();
            return false;
        });

        // Additional protection: disable selection
        document.onselectstart = function() {
            return false;
        };
    </script>
</body>
</html>

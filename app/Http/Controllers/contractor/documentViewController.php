<?php

namespace App\Http\Controllers\contractor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentViewController extends Controller
{
    /**
     * Display a protected document with watermark overlay
     */
    public function viewProtectedDocument(Request $request)
    {
        $filePath = $request->query('file');
        $documentType = $request->query('type', 'Document');

        // Validate file path
        if (!$filePath) {
            abort(404, 'Document not found');
        }

        // Security: Ensure the file path doesn't contain directory traversal
        if (str_contains($filePath, '..') || str_contains($filePath, '//')) {
            abort(403, 'Invalid file path');
        }

        // Ensure file exists
        $fullPath = storage_path('app/public/' . $filePath);
        if (!file_exists($fullPath)) {
            abort(404, 'Document not found');
        }

        // Generate the document URL
        $documentUrl = asset('storage/' . ltrim($filePath, '/'));

        return view('contractor.protected_document_viewer', [
            'documentUrl' => $documentUrl,
            'documentType' => $documentType,
            'filePath' => $filePath
        ]);
    }
}

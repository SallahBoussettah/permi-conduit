<?php

namespace App\Http\Controllers\Inspector;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Spatie\PdfToImage\Pdf;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
// Keep the Imagick import but don't directly reference it to allow runtime check

class CourseMaterialController extends Controller
{
    /**
     * Display a listing of the course materials.
     */
    public function index(Course $course)
    {
        $materials = $course->materials()->orderBy('sequence_order', 'asc')->paginate(10);
        return view('inspector.materials.index', compact('course', 'materials'));
    }

    /**
     * Show the form for creating a new course material.
     */
    public function create(Course $course)
    {
        return view('inspector.materials.create', compact('course'));
    }

    /**
     * Store a newly created course material in storage.
     */
    public function store(Request $request, Course $course)
    {
        // Debug upload information 
        Log::info('Audio upload attempt - UPDATED METHOD', [
            'POST size' => $request->header('Content-Length'),
            'Max allowed' => $this->returnBytes(ini_get('post_max_size')),
            'Upload max filesize' => ini_get('upload_max_filesize'),
            'Laravel config filesize' => config('filesystems.max_upload_size', '40M'),
            'Request has audio_file' => $request->hasFile('audio_file'),
            'Request all files' => $request->allFiles(),
            'Request method' => $request->method()
        ]);

        // Enhanced error checking for audio files
        if ($request->material_type === 'audio') {
            if (!$request->hasFile('audio_file')) {
                Log::error('Audio file missing from request', [
                    'request_files' => $request->files->all(),
                    'post_data_keys' => array_keys($request->post()),
                    'content_length' => $request->header('Content-Length')
                ]);
                
                return redirect()->back()
                    ->withErrors(['audio_file' => 'No audio file was found in the request. This could be due to file size exceeding server limits.'])
                    ->withInput();
            }
            
            $audioFile = $request->file('audio_file');
            
            if (!$audioFile->isValid()) {
                $errorMessage = $this->getUploadErrorMessage($audioFile->getError());
                Log::error('Invalid audio file upload', [
                    'error_code' => $audioFile->getError(),
                    'error_message' => $errorMessage
                ]);
                
                return redirect()->back()
                    ->withErrors(['audio_file' => 'Audio file upload failed: ' . $errorMessage])
                    ->withInput();
            }
            
            // Enhanced logging for file details
            Log::info('Audio file details before validation', [
                'original_name' => $audioFile->getClientOriginalName(),
                'size' => $audioFile->getSize(),
                'size_in_mb' => round($audioFile->getSize() / (1024 * 1024), 2) . 'MB',
                'mime_type' => $audioFile->getMimeType()
            ]);
        }

        // Define validation rules based on material type
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'material_type' => 'required|in:pdf,video,audio',
        ];

        // Add specific rules based on material type
        if ($request->material_type === 'pdf') {
            $rules['pdf_file'] = 'required|file|mimes:pdf|max:10240'; // 10MB max
            $rules['thumbnail'] = 'nullable|image|mimes:jpeg,png,jpg|max:2048'; // 2MB max
        } else if ($request->material_type === 'audio') {
            // Using broader validation to accept more audio types
            $rules['audio_file'] = 'required|file|max:41000'; // 40MB max
            $rules['audio_thumbnail'] = 'nullable|image|mimes:jpeg,png,jpg|max:2048'; // 2MB max
        } else { // video
            $rules['video_url'] = 'required|url';
            $rules['video_thumbnail'] = 'nullable|image|mimes:jpeg,png,jpg|max:2048'; // 2MB max
        }

        $validator = Validator::make($request->all(), $rules);

        // Custom validation for YouTube URL
        $validator->after(function ($validator) use ($request) {
            if ($request->material_type === 'video' && $request->has('video_url')) {
                $videoId = $this->extractYoutubeId($request->video_url);
                if (!$videoId) {
                    $validator->errors()->add('video_url', 'The URL must be a valid YouTube video URL.');
                }
            }
        });

        // Get the current PHP upload limit in bytes
        $maxUploadSize = $this->getMaxUploadSize();
        
        // Directly check the size of the audio file if it exists
        if ($request->material_type === 'audio' && $request->hasFile('audio_file')) {
            $audioFile = $request->file('audio_file');
            
            if ($audioFile->getSize() > $maxUploadSize) {
                $maxUploadSizeMB = round($maxUploadSize / (1024 * 1024), 1);
                return redirect()->back()
                    ->withErrors(['audio_file' => "The audio file exceeds the maximum upload size of {$maxUploadSizeMB}MB. Please choose a smaller file."])
                    ->withInput();
            }
        }

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Get the next sequence order
        $nextOrder = $course->materials()->max('sequence_order') + 1;

        // Initialize material data
        $materialData = [
            'course_id' => $course->id,
            'title' => $request->title,
            'description' => $request->description,
            'sequence_order' => $nextOrder,
        ];

        // Handle based on material type
        if ($request->material_type === 'pdf') {
            // Handle PDF file upload
            if ($request->hasFile('pdf_file')) {
                $pdf = $request->file('pdf_file');
                $pdfName = time() . '_' . Str::slug($request->title) . '.' . $pdf->getClientOriginalExtension();
                $pdf->storeAs('public/pdfs', $pdfName);
                
                // Get page count
                $pageCount = $this->getPdfPageCount($pdf->path());
                
                // Generate thumbnail or use custom thumbnail
                $thumbnailPath = null;
                if ($request->hasFile('thumbnail')) {
                    $thumbnail = $request->file('thumbnail');
                    $thumbnailName = time() . '_' . Str::slug($request->title) . '.' . $thumbnail->getClientOriginalExtension();
                    $thumbnail->storeAs('public/thumbnails', $thumbnailName);
                    $thumbnailPath = 'thumbnails/' . $thumbnailName;
                } else {
                    // Generate thumbnail from PDF first page
                    $thumbnailPath = $this->generatePdfThumbnail($pdf->path(), $request->title);
                }

                // Set PDF-specific data
                $materialData['material_type'] = 'pdf';
                $materialData['content_path_or_url'] = $pdfName;
                $materialData['thumbnail_path'] = $thumbnailPath;
                $materialData['page_count'] = $pageCount;
            } else {
                return redirect()->back()
                    ->withErrors(['pdf_file' => 'Failed to upload PDF file.'])
                    ->withInput();
            }
        } else if ($request->material_type === 'audio') {
            // Handle audio file upload
            if ($request->hasFile('audio_file')) {
                try {
                    $audio = $request->file('audio_file');
                    
                    // Detailed debug info
                    Log::info('Audio file details', [
                        'original_name' => $audio->getClientOriginalName(),
                        'mime_type' => $audio->getMimeType(),
                        'size' => $audio->getSize(),
                        'size_in_mb' => round($audio->getSize() / (1024 * 1024), 2) . 'MB',
                        'is_valid' => $audio->isValid(),
                        'error' => $audio->getError(),
                        'error_message' => $this->getUploadErrorMessage($audio->getError())
                    ]);

                    // Check the file error code
                    if ($audio->getError() !== UPLOAD_ERR_OK) {
                        throw new \Exception('File upload error: ' . $this->getUploadErrorMessage($audio->getError()));
                    }
                    
                    // Make sure the file is valid
                    if (!$audio->isValid()) {
                        throw new \Exception('Invalid audio file upload: Error code ' . $audio->getError());
                    }
                    
                    $audioName = time() . '_' . Str::slug($request->title) . '.' . $audio->getClientOriginalExtension();
                    
                    // Make sure the audio directory exists
                    $audioDir = storage_path('app/public/audio');
                    if (!file_exists($audioDir)) {
                        mkdir($audioDir, 0755, true);
                        Log::info('Created audio directory: ' . $audioDir);
                    }
                    
                    // Check directory permissions
                    Log::info('Directory permissions check', [
                        'audio_dir' => $audioDir,
                        'exists' => file_exists($audioDir),
                        'is_writable' => is_writable($audioDir)
                    ]);
                    
                    // Store the file
                    Log::info('Attempting to store audio file', [
                        'path' => 'public/audio/' . $audioName,
                        'disk' => config('filesystems.default')
                    ]);
                    
                    $path = $audio->storeAs('public/audio', $audioName);
                    if (!$path) {
                        throw new \Exception('Failed to store audio file. Check storage permissions.');
                    }
                    
                    Log::info('Audio file stored successfully at: ' . $path);
                    
                    // Get audio duration if possible (this would require a library like getID3)
                    $durationSeconds = null;
                    
                    // Generate thumbnail or use custom thumbnail
                    $thumbnailPath = null;
                    if ($request->hasFile('audio_thumbnail')) {
                        $thumbnail = $request->file('audio_thumbnail');
                        $thumbnailName = time() . '_' . Str::slug($request->title) . '.' . $thumbnail->getClientOriginalExtension();
                        $thumbnail->storeAs('public/thumbnails', $thumbnailName);
                        $thumbnailPath = 'thumbnails/' . $thumbnailName;
                    } else {
                        // Use a default audio thumbnail
                        $thumbnailPath = 'thumbnails/default_audio.jpg';
                        
                        // Make sure the default thumbnail exists
                        $defaultThumbPath = storage_path('app/public/' . $thumbnailPath);
                        if (!file_exists($defaultThumbPath)) {
                            // Create a simple audio icon
                            $this->createDefaultAudioThumbnail($defaultThumbPath);
                        }
                    }
    
                    // Set audio-specific data
                    $materialData['material_type'] = 'audio';
                    $materialData['content_path_or_url'] = $audioName;
                    $materialData['thumbnail_path'] = $thumbnailPath;
                    $materialData['duration_seconds'] = $durationSeconds;
                } catch (\Exception $e) {
                    Log::error('Audio file upload failed: ' . $e->getMessage(), [
                        'exception' => $e,
                        'trace' => $e->getTraceAsString()
                    ]);
                    return redirect()->back()
                        ->withErrors(['audio_file' => 'Failed to upload audio file: ' . $e->getMessage()])
                        ->withInput();
                }
            } else {
                return redirect()->back()
                    ->withErrors(['audio_file' => 'No audio file was selected for upload.'])
                    ->withInput();
            }
        } else { // video
            // Process YouTube URL
            $videoUrl = $request->video_url;
            $videoId = $this->extractYoutubeId($videoUrl);

            if (!$videoId) {
                return redirect()->back()
                    ->withErrors(['video_url' => 'Invalid YouTube URL. Please enter a valid YouTube video URL.'])
                    ->withInput();
            }

            // Generate or use custom thumbnail
            $thumbnailPath = null;
            if ($request->hasFile('video_thumbnail')) {
                $thumbnail = $request->file('video_thumbnail');
                $thumbnailName = time() . '_' . Str::slug($request->title) . '.' . $thumbnail->getClientOriginalExtension();
                $thumbnail->storeAs('public/thumbnails', $thumbnailName);
                $thumbnailPath = 'thumbnails/' . $thumbnailName;
            } else {
                // Use default video thumbnail from storage/thumbnails
                $thumbnailPath = 'thumbnails/default_video.jpg';
            }

            // Set video-specific data
            $materialData['material_type'] = 'video';
            $materialData['content_path_or_url'] = $videoId; // Store just the YouTube ID
            $materialData['thumbnail_path'] = $thumbnailPath;
        }
        
        // Create course material
        $material = new CourseMaterial($materialData);
        $material->save();
        
        return redirect()->route('inspector.courses.show', $course)
            ->with('success', 'Course material added successfully.');
    }

    /**
     * Extract YouTube video ID from a YouTube URL
     * 
     * @param string $url YouTube URL
     * @return string|null YouTube video ID or null if invalid
     */
    private function extractYoutubeId($url)
    {
        // Fix the pattern by using a different delimiter (# instead of /)
        // This avoids conflicts with the slashes in the URL pattern
        $pattern = '#(?:youtube\.com/(?:[^/\n\s]+/\S+/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be/)([a-zA-Z0-9_-]{11})#';
        
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }
        
        return null;
    }

    /**
     * Get the page count of a PDF file.
     */
    private function getPdfPageCount($pdfPath)
    {
        try {
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($pdfPath);
            return count($pdf->getPages());
        } catch (\Exception $e) {
            Log::error('Failed to get PDF page count: ' . $e->getMessage());
            return 0; // Default to 0 if we can't determine the page count
        }
    }

    /**
     * Generate a thumbnail from the first page of a PDF.
     */
    private function generatePdfThumbnail($pdfPath, $title)
    {
        try {
            // Try to generate thumbnail from PDF
            if (extension_loaded('imagick') && class_exists('Imagick')) {
                $imagick = new \Imagick();
                $imagick->setResolution(150, 150);
                $imagick->readImage($pdfPath . '[0]'); // Read first page only
                $imagick->setImageFormat('png');
                $imagick->setImageBackgroundColor('white');
                $imagick->setImageAlphaChannel(\Imagick::ALPHACHANNEL_REMOVE);
                $imagick->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
                $imagick->setImageFormat('jpg');
                $imagick->thumbnailImage(300, 400, true);
                
                $thumbnailFileName = time() . '_' . Str::slug($title) . '_thumb.jpg';
                $thumbnailPath = 'thumbnails/' . $thumbnailFileName;
                $fullThumbnailPath = storage_path('app/public/' . $thumbnailPath);
                
                // Ensure the thumbnails directory exists
                if (!file_exists(dirname($fullThumbnailPath))) {
                    mkdir(dirname($fullThumbnailPath), 0755, true);
                }
                
                $imagick->writeImage($fullThumbnailPath);
                $imagick->clear();
                $imagick->destroy();
                
                return $thumbnailPath;
            }
        } catch (\Exception $e) {
            Log::error('Thumbnail generation failed: ' . $e->getMessage());
        }
        
        // If we get here, either Imagick isn't available or thumbnail generation failed
        // Use a default thumbnail
        return 'thumbnails/default_pdf.png';
    }

    /**
     * Create a default PDF thumbnail image.
     */
    private function createDefaultPdfThumbnail($path)
    {
        try {
            // Create a simple PDF icon image
            $width = 300;
            $height = 400;
            $image = imagecreatetruecolor($width, $height);
            
            // Set background color (light gray)
            $bgColor = imagecolorallocate($image, 240, 240, 240);
            imagefill($image, 0, 0, $bgColor);
            
            // Draw PDF icon (document with folded corner)
            $docColor = imagecolorallocate($image, 255, 255, 255);
            $borderColor = imagecolorallocate($image, 200, 200, 200);
            $foldColor = imagecolorallocate($image, 220, 220, 220);
            $textColor = imagecolorallocate($image, 220, 0, 0);
            
            // Document
            imagefilledrectangle($image, 50, 50, $width - 50, $height - 50, $docColor);
            imagerectangle($image, 50, 50, $width - 50, $height - 50, $borderColor);
            
            // Folded corner
            imagefilledpolygon($image, [$width - 100, 50, $width - 50, 100, $width - 50, 50], 3, $foldColor);
            imagepolygon($image, [$width - 100, 50, $width - 50, 100, $width - 50, 50], 3, $borderColor);
            
            // PDF text
            imagestring($image, 5, $width/2 - 20, $height/2 - 10, "PDF", $textColor);
            
            // Ensure the directory exists
            $fullPath = storage_path('app/public/' . $path);
            if (!file_exists(dirname($fullPath))) {
                mkdir(dirname($fullPath), 0755, true);
            }
            
            // Save the image
            imagepng($image, $fullPath);
            imagedestroy($image);
            
            Log::info('Default PDF thumbnail created at ' . $fullPath);
            return true;
        } catch (\Exception $e) {
            Log::error('Default PDF thumbnail creation failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Display the specified course material.
     */
    public function show(Course $course, CourseMaterial $material)
    {
        return view('inspector.materials.show', compact('course', 'material'));
    }

    /**
     * Show the form for editing the specified course material.
     */
    public function edit(Course $course, CourseMaterial $material)
    {
        return view('inspector.materials.edit', compact('course', 'material'));
    }

    /**
     * Update the specified course material in storage.
     */
    public function update(Request $request, Course $course, CourseMaterial $material)
    {
        // Basic validation for all material types
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'material_type' => 'required|in:pdf,video,audio',
            'custom_thumbnail' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // 2MB max
        ];

        // Add specific validation rules based on material type
        if ($request->material_type === 'pdf') {
            $rules['pdf_file'] = 'nullable|file|mimes:pdf|max:10240'; // 10MB max
        } else if ($request->material_type === 'audio') {
            // Using broader validation for audio files
            $rules['audio_file'] = 'nullable|file|max:40960'; // 40MB max
        } else { // video
            $rules['video_url'] = 'required|url';
        }

        $validator = Validator::make($request->all(), $rules);

        // Custom validation for YouTube URL
        $validator->after(function ($validator) use ($request) {
            if ($request->material_type === 'video' && $request->has('video_url')) {
                $videoId = $this->extractYoutubeId($request->video_url);
                if (!$videoId) {
                    $validator->errors()->add('video_url', 'The URL must be a valid YouTube video URL.');
                }
            }
        });
        
        // Get the current PHP upload limit in bytes
        $maxUploadSize = $this->getMaxUploadSize();
        
        // Directly check the size of the audio file if it exists
        if ($request->material_type === 'audio' && $request->hasFile('audio_file')) {
            $audioFile = $request->file('audio_file');
            
            if ($audioFile->getSize() > $maxUploadSize) {
                $maxUploadSizeMB = round($maxUploadSize / (1024 * 1024), 1);
                return redirect()->back()
                    ->withErrors(['audio_file' => "The audio file exceeds the maximum upload size of {$maxUploadSizeMB}MB. Please choose a smaller file."])
                    ->withInput();
            }
        }

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Update basic information
        $material->title = $request->title;
        $material->description = $request->description;
        
        // IMPORTANT: Set the material_type from the request
        $material->material_type = $request->material_type;

        // Handle material type specific updates
        if ($request->material_type === 'pdf') {
            // Replace PDF file if provided
            if ($request->hasFile('pdf_file')) {
                // Delete old PDF
                Storage::delete('public/pdfs/' . $material->content_path_or_url);
                
                // Upload new PDF
                $pdf = $request->file('pdf_file');
                $pdfName = time() . '_' . Str::slug($request->title) . '.' . $pdf->getClientOriginalExtension();
                $pdf->storeAs('public/pdfs', $pdfName);
                
                // Update PDF-related fields
                $material->content_path_or_url = $pdfName;
                $material->page_count = $this->getPdfPageCount($pdf->path());
                
                // Generate new thumbnail if not provided
                if (!$request->hasFile('custom_thumbnail')) {
                    // Delete old thumbnail
                    if ($material->thumbnail_path) {
                        Storage::delete('public/' . $material->thumbnail_path);
                    }
                    
                    // Generate new thumbnail
                    $material->thumbnail_path = $this->generatePdfThumbnail($pdf->path(), $request->title);
                }
            }
        } else if ($request->material_type === 'audio') {
            // Replace audio file if provided
            if ($request->hasFile('audio_file')) {
                // Delete old audio file
                if ($material->content_path_or_url) {
                    Storage::delete('public/audio/' . $material->content_path_or_url);
                }
                
                // Upload new audio file
                $audio = $request->file('audio_file');
                $audioName = time() . '_' . Str::slug($request->title) . '.' . $audio->getClientOriginalExtension();
                
                // Make sure the audio directory exists
                $audioDir = storage_path('app/public/audio');
                if (!file_exists($audioDir)) {
                    mkdir($audioDir, 0755, true);
                }
                
                $audio->storeAs('public/audio', $audioName);
                
                // Update audio-related fields
                $material->content_path_or_url = $audioName;
                
                // Get audio duration if possible (this would require a library like getID3)
                $durationSeconds = null;
                $material->duration_seconds = $durationSeconds;
                
                // Update thumbnail only if not provided by user
                if (!$request->hasFile('custom_thumbnail')) {
                    // Delete old thumbnail
                    if ($material->thumbnail_path) {
                        Storage::delete('public/' . $material->thumbnail_path);
                    }
                    
                    // Use a default audio thumbnail
                    $thumbnailPath = 'thumbnails/default_audio.jpg';
                    
                    // Make sure the default thumbnail exists
                    $defaultThumbPath = storage_path('app/public/' . $thumbnailPath);
                    if (!file_exists($defaultThumbPath)) {
                        // Create a simple audio icon
                        $this->createDefaultAudioThumbnail($defaultThumbPath);
                    }
                    
                    $material->thumbnail_path = $thumbnailPath;
                }
            }
        } else { // video
            // Update YouTube URL if changed
            if ($request->has('video_url')) {
                $videoUrl = $request->video_url;
                $videoId = $this->extractYoutubeId($videoUrl);
                
                if (!$videoId) {
                    return redirect()->back()
                        ->withErrors(['video_url' => 'Invalid YouTube URL. Please enter a valid YouTube video URL.'])
                        ->withInput();
                }
                
                // Update the video ID
                $material->content_path_or_url = $videoId;
                    
                // Update thumbnail only if not provided by user
                if (!$request->hasFile('custom_thumbnail')) {
                    // Delete old thumbnail
                    if ($material->thumbnail_path) {
                        Storage::delete('public/' . $material->thumbnail_path);
                    }
                    
                    // Use default video thumbnail from storage/thumbnails
                    $thumbnailPath = 'thumbnails/default_video.jpg';
                    $material->thumbnail_path = $thumbnailPath;
                }
            }
        }

        // Handle custom thumbnail if provided
        if ($request->hasFile('custom_thumbnail')) {
            // Delete old thumbnail
            if ($material->thumbnail_path) {
                Storage::delete('public/' . $material->thumbnail_path);
            }
            
            $thumbnail = $request->file('custom_thumbnail');
            $thumbnailName = time() . '_' . Str::slug($request->title) . '.' . $thumbnail->getClientOriginalExtension();
            $thumbnail->storeAs('public/thumbnails', $thumbnailName);
            $material->thumbnail_path = 'thumbnails/' . $thumbnailName;
        }
        
        $material->save();

        return redirect()->route('inspector.courses.show', $course)
            ->with('success', 'Course material updated successfully.');
    }

    /**
     * Remove the specified course material from storage.
     */
    public function destroy(Course $course, CourseMaterial $material)
    {
        // Delete the relevant file based on material type
        if ($material->material_type === 'pdf' && $material->content_path_or_url) {
            Storage::delete('public/pdfs/' . $material->content_path_or_url);
        } else if ($material->material_type === 'audio' && $material->content_path_or_url) {
            Storage::delete('public/audio/' . $material->content_path_or_url);
        }
        // For video type, there's no file to delete as we only store the YouTube ID
        
        // Delete thumbnail
        if ($material->thumbnail_path) {
            Storage::delete('public/' . $material->thumbnail_path);
        }
        
        // Delete the material
        $material->delete();
        
        // Reorder remaining materials
        $remainingMaterials = $course->materials()
            ->orderBy('sequence_order', 'asc')
            ->get();
            
        $sequence = 1;
        foreach ($remainingMaterials as $remaining) {
            $remaining->sequence_order = $sequence++;
            $remaining->save();
        }

        return redirect()->route('inspector.courses.show', $course)
            ->with('success', 'Course material deleted successfully.');
    }
    
    /**
     * Update the order of course materials
     */
    public function updateOrder(Request $request, Course $course)
    {
        $validator = Validator::make($request->all(), [
            'materials' => 'required|array',
            'materials.*' => 'required|exists:course_materials,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        foreach ($request->materials as $index => $id) {
            CourseMaterial::where('id', $id)->update(['sequence_order' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Serve the PDF file for the specified course material.
     */
    public function servePdf(Course $course, CourseMaterial $material)
    {
        // Check if file exists
        $filePath = 'public/pdfs/' . $material->content_path_or_url;
        
        if (!Storage::exists($filePath)) {
            abort(404, 'PDF file not found');
        }
        
        // Get file content
        $fileContent = Storage::get($filePath);
        
        // Return file as response
        return Response::make($fileContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $material->title . '.pdf"',
        ]);
    }

    /**
     * Serve audio file.
     *
     * @param  \App\Models\Course  $course
     * @param  \App\Models\CourseMaterial  $material
     * @return \Illuminate\Http\Response
     */
    public function serveAudio(Course $course, CourseMaterial $material)
    {
        // Check if file exists
        $filePath = 'public/audio/' . $material->content_path_or_url;
        
        if (!Storage::exists($filePath)) {
            abort(404, 'Audio file not found');
        }
        
        // Get the file
        $file = Storage::path($filePath);
        $type = mime_content_type($file);
        
        // Return as a streaming download
        return response()->file($file, [
            'Content-Type' => $type,
            'Content-Disposition' => 'inline; filename="' . $material->title . '"',
        ]);
    }

    /**
     * Create a default audio thumbnail image.
     */
    private function createDefaultAudioThumbnail($path)
    {
        try {
            // Create a simple audio icon image
            $width = 300;
            $height = 300;
            $image = imagecreatetruecolor($width, $height);
            
            // Set background color (light gray)
            $bgColor = imagecolorallocate($image, 240, 240, 240);
            imagefill($image, 0, 0, $bgColor);
            
            // Set colors for audio icon
            $iconColor = imagecolorallocate($image, 235, 180, 50); // Yellow/Gold
            $textColor = imagecolorallocate($image, 70, 70, 70); // Dark gray
            
            // Draw a circle for audio icon
            $centerX = $width / 2;
            $centerY = $height / 2;
            $radius = min($width, $height) * 0.3;
            imagefilledellipse($image, $centerX, $centerY, $radius * 2, $radius * 2, $iconColor);
            
            // Draw inner circle (to make it look like a speaker or note)
            imagefilledellipse($image, $centerX, $centerY, $radius, $radius, $bgColor);
            
            // Draw audio wave lines
            $lineColor = imagecolorallocate($image, 70, 70, 70);
            $waveWidth = $radius * 1.2;
            $waveHeight = $radius * 0.8;
            
            // Draw three wave lines
            for ($i = 0; $i < 3; $i++) {
                $xOffset = $centerX + ($i - 1) * ($waveWidth / 4);
                imagesetthickness($image, 3);
                imageline($image, $xOffset, $centerY - $waveHeight/2, $xOffset, $centerY + $waveHeight/2, $lineColor);
            }
            
            // Add "AUDIO" text
            imagestring($image, 5, $width/2 - 25, $height/2 + $radius + 20, "AUDIO", $textColor);
            
            // Create directory if needed
            if (!file_exists(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }
            
            // Save the image as JPEG (better for thumbnails)
            imagejpeg($image, $path, 90);
            imagedestroy($image);
            
            Log::info('Default audio thumbnail created at ' . $path);
            return true;
        } catch (\Exception $e) {
            Log::error('Default audio thumbnail creation failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get the maximum upload size allowed by PHP configuration
     * @return int Maximum upload size in bytes
     */
    private function getMaxUploadSize()
    {
        // Get PHP's upload_max_filesize and post_max_size in bytes
        $uploadMaxFilesize = $this->returnBytes(ini_get('upload_max_filesize'));
        $postMaxSize = $this->returnBytes(ini_get('post_max_size'));
        
        // The smaller of the two values
        return min($uploadMaxFilesize, $postMaxSize);
    }
    
    /**
     * Convert a PHP ini value to bytes
     * @param string $val Value from ini_get (e.g. "2M", "8M")
     * @return int Value in bytes
     */
    private function returnBytes($val)
    {
        $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);
        $val = (int)$val;
        
        switch($last) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }
        
        return $val;
    }

    /**
     * Get a human-readable error message for upload error codes
     */
    private function getUploadErrorMessage($errorCode) {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
            case UPLOAD_ERR_FORM_SIZE:
                return 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form';
            case UPLOAD_ERR_PARTIAL:
                return 'The uploaded file was only partially uploaded';
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing a temporary folder';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk';
            case UPLOAD_ERR_EXTENSION:
                return 'A PHP extension stopped the file upload';
            default:
                return 'Unknown upload error';
        }
    }

    /**
     * Create a default video thumbnail image.
     */
    private function createDefaultVideoThumbnail($path)
    {
        try {
            // Create a simple video icon image
            $width = 300;
            $height = 200;
            $image = imagecreatetruecolor($width, $height);
            
            // Set background color (dark gray)
            $bgColor = imagecolorallocate($image, 45, 45, 45);
            imagefill($image, 0, 0, $bgColor);
            
            // Draw video play button icon
            $iconColor = imagecolorallocate($image, 255, 0, 0);
            $triangleColor = imagecolorallocate($image, 255, 255, 255);
            
            // Circle for play button
            imagefilledellipse($image, $width/2, $height/2, 80, 80, $iconColor);
            
            // Triangle for play symbol
            $centerX = $width/2;
            $centerY = $height/2;
            $trianglePoints = [
                $centerX - 15, $centerY - 25,
                $centerX - 15, $centerY + 25,
                $centerX + 30, $centerY
            ];
            imagefilledpolygon($image, $trianglePoints, 3, $triangleColor);
            
            // Ensure the directory exists
            $fullPath = $path;
            if (!file_exists(dirname($fullPath))) {
                mkdir(dirname($fullPath), 0755, true);
            }
            
            // Save the image
            imagejpeg($image, $fullPath, 90);
            imagedestroy($image);
            
            Log::info('Default video thumbnail created at ' . $fullPath);
            return true;
        } catch (\Exception $e) {
            Log::error('Default video thumbnail creation failed: ' . $e->getMessage());
            return false;
        }
    }
}

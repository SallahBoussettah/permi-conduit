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
        // Define validation rules based on material type
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'material_type' => 'required|in:pdf,video',
        ];

        // Add specific rules based on material type
        if ($request->material_type === 'pdf') {
            $rules['pdf_file'] = 'required|file|mimes:pdf|max:10240'; // 10MB max
            $rules['thumbnail'] = 'nullable|image|mimes:jpeg,png,jpg|max:2048'; // 2MB max
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
                // Use YouTube thumbnail
                $thumbnailPath = 'thumbnails/youtube_' . $videoId . '.jpg';
                
                // Download YouTube thumbnail if it doesn't exist
                $youtubeThumbUrl = "https://img.youtube.com/vi/{$videoId}/maxresdefault.jpg";
                $localThumbPath = storage_path('app/public/' . $thumbnailPath);
                
                // Create directory if it doesn't exist
                if (!file_exists(dirname($localThumbPath))) {
                    mkdir(dirname($localThumbPath), 0755, true);
                }
                
                // Try to download the maxresdefault thumbnail (HD)
                if (!@copy($youtubeThumbUrl, $localThumbPath)) {
                    // If HD thumbnail not available, use the default thumbnail
                    $youtubeThumbUrl = "https://img.youtube.com/vi/{$videoId}/0.jpg";
                    @copy($youtubeThumbUrl, $localThumbPath);
                }
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
            'material_type' => 'required|in:pdf,video',
            'custom_thumbnail' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // 2MB max
        ];

        // Add specific validation rules based on material type
        if ($request->material_type === 'pdf') {
            $rules['pdf_file'] = 'nullable|file|mimes:pdf|max:10240'; // 10MB max
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
                    
                    // Use YouTube thumbnail
                    $thumbnailPath = 'thumbnails/youtube_' . $videoId . '.jpg';
                    
                    // Download YouTube thumbnail if it doesn't exist
                    $youtubeThumbUrl = "https://img.youtube.com/vi/{$videoId}/maxresdefault.jpg";
                    $localThumbPath = storage_path('app/public/' . $thumbnailPath);
                    
                    // Create directory if it doesn't exist
                    if (!file_exists(dirname($localThumbPath))) {
                        mkdir(dirname($localThumbPath), 0755, true);
                    }
                    
                    // Try to download the maxresdefault thumbnail (HD)
                    if (!@copy($youtubeThumbUrl, $localThumbPath)) {
                        // If HD thumbnail not available, use the default thumbnail
                        $youtubeThumbUrl = "https://img.youtube.com/vi/{$videoId}/0.jpg";
                        @copy($youtubeThumbUrl, $localThumbPath);
                    }
                    
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
        // Delete PDF file
        if ($material->content_path_or_url) {
            Storage::delete('public/pdfs/' . basename($material->content_path_or_url));
        }
        
        // Delete thumbnail
        if ($material->thumbnail_path) {
            Storage::delete('public/thumbnails/' . basename($material->thumbnail_path));
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
}

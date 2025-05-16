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
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'pdf_file' => 'required|file|mimes:pdf|max:10240', // 10MB max
            'custom_thumbnail' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // 2MB max
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Handle PDF file upload
        if ($request->hasFile('pdf_file')) {
            $pdf = $request->file('pdf_file');
            $pdfName = time() . '_' . Str::slug($request->title) . '.' . $pdf->getClientOriginalExtension();
            $pdf->storeAs('public/pdfs', $pdfName);
            
            // Get page count
            $pageCount = $this->getPdfPageCount($pdf->path());
            
            // Generate thumbnail or use custom thumbnail
            $thumbnailPath = null;
            if ($request->hasFile('custom_thumbnail')) {
                $thumbnail = $request->file('custom_thumbnail');
                $thumbnailName = time() . '_' . Str::slug($request->title) . '.' . $thumbnail->getClientOriginalExtension();
                $thumbnail->storeAs('public/thumbnails', $thumbnailName);
                $thumbnailPath = 'thumbnails/' . $thumbnailName;
            } else {
                // Generate thumbnail from PDF first page
                $thumbnailPath = $this->generatePdfThumbnail($pdf->path(), $request->title);
            }

            // Get the next sequence order
            $nextOrder = $course->materials()->max('sequence_order') + 1;
        
            // Create course material
            $material = new CourseMaterial([
                'course_id' => $course->id,
                'title' => $request->title,
                'description' => $request->description,
                'content_path_or_url' => $pdfName,
                'thumbnail_path' => $thumbnailPath,
                'page_count' => $pageCount,
                'sequence_order' => $nextOrder,
            ]);
            
            $material->save();
            
            return redirect()->route('inspector.courses.show', $course)
                ->with('success', 'Course material added successfully.');
        }
        
        return redirect()->back()
            ->withErrors(['pdf_file' => 'Failed to upload PDF file.'])
            ->withInput();
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
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'pdf_file' => 'nullable|file|mimes:pdf|max:10240', // 10MB max
            'custom_thumbnail' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // 2MB max
            'sequence_order' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Update basic info
        $material->title = $request->title;
        $material->description = $request->description;
        
        // Update sequence order if provided
        if ($request->filled('sequence_order')) {
            $material->sequence_order = $request->sequence_order;
        }
        
        // Handle PDF upload if a new file is provided
        if ($request->hasFile('pdf_file')) {
            // Delete old PDF
            if ($material->content_path_or_url) {
                Storage::delete('public/pdfs/' . basename($material->content_path_or_url));
            }
            
            $pdf = $request->file('pdf_file');
            $pdfName = time() . '_' . Str::slug($request->title) . '.' . $pdf->getClientOriginalExtension();
            $pdf->storeAs('public/pdfs', $pdfName);
            
            // Get total pages in PDF
            try {
                // Check if Imagick is available
                if (class_exists('Imagick')) {
                    $imagick = new \Imagick();
                    $imagick->pingImage($pdf->getPathname());
                    $totalPages = $imagick->getNumberImages();
                    $material->page_count = $totalPages;
                    $imagick->clear();
                } else {
                    // If Imagick is not available, set a default value
                    $material->page_count = 1;
                    Log::info('Imagick not available - using default page count for update');
                }
            } catch (\Exception $e) {
                // If unable to determine page count, default to 1
                $material->page_count = 1;
                Log::error('Error determining PDF page count on update: ' . $e->getMessage());
            }
            
            // Update content path or URL
            $material->content_path_or_url = $pdfName;
            
            // Try to generate new thumbnail if none provided
            if (!$request->hasFile('custom_thumbnail')) {
                try {
                    // Only attempt thumbnail generation if Imagick is available
                    if (class_exists('Imagick')) {
                        $tempPath = storage_path('app/temp/' . Str::random(16));
                        if (!is_dir(storage_path('app/temp'))) {
                            mkdir(storage_path('app/temp'), 0755, true);
                        }
                        
                        $imagick = new \Imagick();
                        $imagick->readImage($pdf->getPathname() . '[0]');  // First page only
                        $imagick->setImageFormat('jpg');
                        $imagick->writeImage($tempPath . '.jpg');
                        $imagick->clear();
                        
                        // Clear existing thumbnail and add the generated one
                        $material->clearMediaCollection('thumbnail');
                        $thumbnail = $material->addMedia($tempPath . '.jpg')
                            ->usingName($material->title . '_thumbnail')
                            ->toMediaCollection('thumbnail');
                            
                            $material->thumbnail_path = $thumbnail->getPath();
                        
                        // Clean up temp file
                        @unlink($tempPath . '.jpg');
                    } else {
                        // Default thumbnail if Imagick is not available
                        try {
                            // Clear existing thumbnail if any
                            $material->clearMediaCollection('thumbnail');
                            
                            $defaultImage = public_path('images/default-pdf-thumbnail.jpg');
                            $thumbnail = $material->addMedia($defaultImage)
                                ->preservingOriginal()
                                ->usingName($material->title . '_default_thumbnail')
                                ->toMediaCollection('thumbnail');
                            
                            $material->thumbnail_path = $thumbnail->getPath();
                            Log::info('Using default thumbnail on update - Imagick not available');
                        } catch (\Exception $ex) {
                            // If adding from media library fails, use direct path
                            $material->thumbnail_path = '/images/default-pdf-thumbnail.jpg';
                            Log::error('Error adding default thumbnail on update: ' . $ex->getMessage());
                        }
                    }
                } catch (\Exception $e) {
                    // If thumbnail generation fails, use a default thumbnail
                    try {
                        // Clear existing thumbnail if any
                        $material->clearMediaCollection('thumbnail');
                        
                        $defaultImage = public_path('images/default-pdf-thumbnail.jpg');
                        $thumbnail = $material->addMedia($defaultImage)
                            ->preservingOriginal()
                            ->usingName($material->title . '_default_thumbnail')
                            ->toMediaCollection('thumbnail');
                        
                        $material->thumbnail_path = $thumbnail->getPath();
                    } catch (\Exception $ex) {
                        // If adding from media library fails, use direct path
                        $material->thumbnail_path = '/images/default-pdf-thumbnail.jpg';
                    }
                    
                    Log::error('Error generating PDF thumbnail on update: ' . $e->getMessage());
                }
            }
        }
        
        // Handle custom thumbnail if provided
        if ($request->hasFile('custom_thumbnail')) {
            // Delete old thumbnail
            if ($material->thumbnail_path) {
                Storage::delete('public/thumbnails/' . basename($material->thumbnail_path));
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

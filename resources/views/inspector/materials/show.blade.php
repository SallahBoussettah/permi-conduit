@extends('layouts.main')

@section('content')
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
                        <div>
                            <h2 class="text-2xl font-semibold text-gray-800">{{ $material->title }}</h2>
                            <p class="text-gray-600">
                                Course: <a href="{{ route('inspector.courses.show', $course) }}" class="text-yellow-600 hover:text-yellow-800">{{ $course->title }}</a>
                            </p>
                        </div>
                        
                        <div class="mt-4 md:mt-0 flex space-x-2">
                            <a href="{{ route('inspector.courses.materials.edit', [$course, $material]) }}" class="px-4 py-2 bg-yellow-500 text-gray-900 rounded hover:bg-yellow-400 active:bg-yellow-600 font-semibold text-xs uppercase tracking-widest">
                                Edit Material
                            </a>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Details</h3>
                        <div class="bg-gray-50 rounded-lg p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-500">Material Type</p>
                                <p class="text-md font-medium">{{ ucfirst($material->material_type) }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Sequence Order</p>
                                <p class="text-md font-medium">{{ $material->sequence_order }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Total Pages</p>
                                <p class="text-md font-medium" id="pdfPageCount">{{ $material->total_pages ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Created At</p>
                                <p class="text-md font-medium">{{ $material->created_at->format('M d, Y') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Description</h3>
                        <div class="prose max-w-none bg-gray-50 rounded-lg p-4">
                            @if($material->description)
                                {{ $material->description }}
                            @else
                                <p class="text-gray-500 italic">No description provided.</p>
                            @endif
                        </div>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Thumbnail</h3>
                        <div class="bg-gray-50 rounded-lg p-4">
                            @if($material->thumbnail_url)
                                @php
                                    // Handle both absolute URLs and relative paths
                                    $thumbnailUrl = $material->thumbnail_url;
                                    if (strpos($thumbnailUrl, 'http') !== 0 && strpos($thumbnailUrl, '//') !== 0) {
                                        // If it's not an absolute URL, make it one
                                        $thumbnailUrl = asset(ltrim($thumbnailUrl, '/'));
                                    }
                                @endphp
                                <img src="{{ $thumbnailUrl }}" alt="{{ $material->title }}" class="h-32 w-32 object-cover rounded border border-gray-200">
                            @else
                                <div class="h-32 w-32 flex items-center justify-center bg-gray-100 text-gray-400 rounded border border-gray-200">
                                    No thumbnail
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Content Preview</h3>
                        <div class="bg-gray-50 rounded-lg p-4">
                            @if($material->material_type === 'pdf')
                                <div id="pdf-container" class="flex flex-col items-center">
                                    <!-- PDF.js viewer container -->
                                    <div class="w-full flex flex-col items-center">
                                        <!-- PDF page navigation -->
                                        <div class="flex items-center space-x-4 mb-4">
                                            <button id="prev" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed">
                                                Previous
                                            </button>
                                            <span>
                                                Page <span id="page-num">1</span> of <span id="page-count">-</span>
                                            </span>
                                            <button id="next" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed">
                                                Next
                                            </button>
                                        </div>
                                        
                                        <!-- PDF rendering canvas -->
                                        <div class="w-full border border-gray-300 rounded-lg bg-white">
                                            <canvas id="pdf-canvas" class="mx-auto"></canvas>
                                        </div>
                                        
                                        <!-- Loading indicator -->
                                        <div id="loading-indicator" class="mt-4 text-gray-600">
                                            Loading PDF...
                                        </div>
                                    </div>
                                    
                                    <div class="mt-4">
                                        <a href="{{ route('inspector.courses.materials.pdf', [$course, $material]) }}" target="_blank" class="px-4 py-2 bg-yellow-500 text-gray-900 rounded hover:bg-yellow-400 active:bg-yellow-600 font-semibold text-xs uppercase tracking-widest">
                                            Open PDF in New Tab
                                        </a>
                                    </div>
                                </div>
                                
                                <!-- PDF.js script -->
                                <script src="https://unpkg.com/pdfjs-dist@3.11.174/build/pdf.min.js"></script>
                                <script>
                                    // Tell PDF.js where to find the worker file
                                    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://unpkg.com/pdfjs-dist@3.11.174/build/pdf.worker.min.js';
                                    
                                    // Use our PDF serving endpoint to avoid CORS issues
                                    const pdfUrl = '{{ route('inspector.courses.materials.pdf', [$course, $material]) }}';
                                    
                                    // Elements
                                    const canvas = document.getElementById('pdf-canvas');
                                    const ctx = canvas.getContext('2d');
                                    const prevButton = document.getElementById('prev');
                                    const nextButton = document.getElementById('next');
                                    const pageNum = document.getElementById('page-num');
                                    const pageCount = document.getElementById('page-count');
                                    const loadingIndicator = document.getElementById('loading-indicator');
                                    
                                    let pdfDoc = null;
                                    let pageIsRendering = false;
                                    let pageNumPending = null;
                                    let currentPage = 1;
                                    let totalPages = 0;
                                    
                                    // Scale factor (adjust as needed)
                                    const scale = 1.5;
                                    
                                    // Render the page
                                    const renderPage = (num) => {
                                        pageIsRendering = true;
                                        
                                        // Get the page
                                        pdfDoc.getPage(num).then(page => {
                                            // Set scale
                                            const viewport = page.getViewport({ scale });
                                            canvas.height = viewport.height;
                                            canvas.width = viewport.width;
                                            
                                            const renderContext = {
                                                canvasContext: ctx,
                                                viewport
                                            };
                                            
                                            page.render(renderContext).promise.then(() => {
                                                pageIsRendering = false;
                                                loadingIndicator.style.display = 'none';
                                                
                                                if (pageNumPending !== null) {
                                                    // New page rendering is pending
                                                    renderPage(pageNumPending);
                                                    pageNumPending = null;
                                                }
                                            });
                                            
                                            // Update page counters
                                            pageNum.textContent = num;
                                        });
                                    };
                                    
                                    // Check for pages rendering
                                    const queueRenderPage = (num) => {
                                        if (pageIsRendering) {
                                            pageNumPending = num;
                                        } else {
                                            renderPage(num);
                                        }
                                    };
                                    
                                    // Show previous page
                                    const showPrevPage = () => {
                                        if (currentPage <= 1) {
                                            return;
                                        }
                                        currentPage--;
                                        queueRenderPage(currentPage);
                                    };
                                    
                                    // Show next page
                                    const showNextPage = () => {
                                        if (currentPage >= totalPages) {
                                            return;
                                        }
                                        currentPage++;
                                        queueRenderPage(currentPage);
                                    };
                                    
                                    // Get the PDF document
                                    pdfjsLib.getDocument(pdfUrl).promise.then(pdfDoc_ => {
                                        pdfDoc = pdfDoc_;
                                        totalPages = pdfDoc.numPages;
                                        
                                        // Update page count display
                                        pageCount.textContent = totalPages;
                                        document.getElementById('pdfPageCount').textContent = totalPages;
                                        
                                        // Enable/disable buttons based on page count
                                        if (totalPages === 1) {
                                            prevButton.disabled = true;
                                            nextButton.disabled = true;
                                        } else {
                                            prevButton.disabled = currentPage === 1;
                                            nextButton.disabled = currentPage === totalPages;
                                        }
                                        
                                        // Render first page
                                        renderPage(currentPage);
                                    }).catch(err => {
                                        // Display error
                                        loadingIndicator.textContent = 'Error loading PDF: ' + err.message;
                                        console.error('Error loading PDF:', err);
                                    });
                                    
                                    // Button events
                                    prevButton.addEventListener('click', () => {
                                        showPrevPage();
                                        // Update button states
                                        prevButton.disabled = currentPage === 1;
                                        nextButton.disabled = false;
                                    });
                                    
                                    nextButton.addEventListener('click', () => {
                                        showNextPage();
                                        // Update button states
                                        nextButton.disabled = currentPage === totalPages;
                                        prevButton.disabled = false;
                                    });
                                </script>
                            @else
                                <p class="text-gray-500">Preview not available for this content type.</p>
                            @endif
                        </div>
                    </div>
                    
                    <div class="mt-8 flex space-x-4">
                        <a href="{{ route('inspector.courses.show', $course) }}" class="text-yellow-600 hover:text-yellow-900">
                            ← Back to Course
                        </a>
                        <form action="{{ route('inspector.courses.materials.destroy', [$course, $material]) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this material? This action cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900">
                                Delete Material
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection 
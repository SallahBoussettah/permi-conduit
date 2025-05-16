@extends('layouts.main')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
            <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-semibold text-gray-800">{{ $material->title }}</h2>
                <div class="flex space-x-2">
                    <a href="{{ route('candidate.courses.show', $course) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                        {{ __('Back to Course') }}
                    </a>
                    <button id="mark-complete-btn" class="inline-flex items-center px-4 py-2 bg-yellow-500 text-gray-900 border border-transparent rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-yellow-400 active:bg-yellow-600 focus:outline-none focus:border-yellow-700 focus:ring ring-yellow-300 disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-150">
                        {{ __('Mark as Complete') }}
                    </button>
                </div>
            </div>

            @if ($material->description)
            <div class="mb-6 bg-gray-50 p-4 rounded-md">
                <p class="text-gray-700">{{ $material->description }}</p>
                        </div>
                    @endif

            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <button id="prev-page" class="px-3 py-1 bg-yellow-500 text-gray-900 rounded-md hover:bg-yellow-400 disabled:opacity-50 disabled:cursor-not-allowed font-semibold text-xs uppercase tracking-widest">{{ __('Previous') }}</button>
                        <button id="next-page" class="px-3 py-1 bg-yellow-500 text-gray-900 rounded-md hover:bg-yellow-400 disabled:opacity-50 disabled:cursor-not-allowed font-semibold text-xs uppercase tracking-widest">{{ __('Next') }}</button>
                    </div>
                    <div class="text-sm">
                        {{ __('Page') }} <span id="page-num">1</span> {{ __('of') }} <span id="page-count">0</span>
                    </div>
                </div>
            </div>
                            
            <div class="border rounded-lg overflow-hidden bg-gray-100">
                <div id="pdf-viewer" class="w-full flex justify-center items-center relative min-h-[600px]">
                    <!-- PDF rendering canvas -->
                    <canvas id="pdf-canvas" class="mx-auto"></canvas>
                    
                    <!-- Loading indicator -->
                    <div id="loading-indicator" class="absolute inset-0 flex flex-col justify-center items-center bg-gray-100 bg-opacity-80">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-yellow-500 mb-4"></div>
                        <p>{{ __('Loading PDF...') }}</p>
                    </div>
                </div>
            </div>
                        
            <div class="mt-6">
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div id="progress-bar" class="bg-yellow-500 h-2.5 rounded-full" style="width: 0%"></div>
                </div>
                <div class="flex justify-between mt-2 text-sm text-gray-600">
                    <span>0%</span>
                    <span id="progress-percentage">0%</span>
                    <span>100%</span>
                </div>
            </div>
                    
            @if ($prevMaterial || $nextMaterial)
            <div class="mt-8 border-t pt-6 flex justify-between items-center">
                <div>
                    @if ($prevMaterial)
                    <a href="{{ route('candidate.courses.materials.show', ['course' => $course, 'material' => $prevMaterial]) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        {{ __('Previous Material') }}
                    </a>
                    @endif
                    </div>
                <div>
                    @if ($nextMaterial)
                    <a href="{{ route('candidate.courses.materials.show', ['course' => $course, 'material' => $nextMaterial]) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                        {{ __('Next Material') }}
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<form id="complete-form" action="{{ route('candidate.courses.materials.complete', ['course' => $course, 'material' => $material]) }}" method="POST" class="hidden">
    @csrf
</form>

<script src="https://unpkg.com/pdfjs-dist@3.11.174/build/pdf.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
        // Initialize PDF.js
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://unpkg.com/pdfjs-dist@3.11.174/build/pdf.worker.min.js';

        const pdfUrl = '{{ route("candidate.courses.materials.pdf", ["course" => $course, "material" => $material]) }}';
        const canvas = document.getElementById('pdf-canvas');
        const ctx = canvas.getContext('2d');
        const pageNum = document.getElementById('page-num');
        const pageCount = document.getElementById('page-count');
        const prevPageButton = document.getElementById('prev-page');
        const nextPageButton = document.getElementById('next-page');
        const progressBar = document.getElementById('progress-bar');
        const progressPercentage = document.getElementById('progress-percentage');
        const completeButton = document.getElementById('mark-complete-btn');
        const completeForm = document.getElementById('complete-form');
        const loadingIndicator = document.getElementById('loading-indicator');

        let currentPage = 1;
        let pdfDoc = null;
        let pageIsRendering = false;
        let pageNumPending = null;
        let totalPages = 0;
        let visitedPages = new Set();

        // Scale factor
        const scale = 1.5;

        // Track progress
        function updateProgress() {
            visitedPages.add(currentPage);
            const progress = Math.floor((visitedPages.size / totalPages) * 100);
            progressBar.style.width = `${progress}%`;
            progressPercentage.textContent = `${progress}%`;
            
            // Send progress update to server
            fetch('{{ route("candidate.courses.materials.progress", ["course" => $course, "material" => $material]) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    completion_percentage: progress
                })
            });

            // If all pages have been viewed, enable the complete button
            if (visitedPages.size === totalPages) {
                enableCompleteButton();
            }
            
            // Check if we're at the last page and unlock the completion button
            if (visitedPages.size >= totalPages || currentPage === totalPages) {
                enableCompleteButton();
            }
        }
        
        // Enable the complete button
        function enableCompleteButton() {
            completeButton.classList.remove('bg-gray-400');
            completeButton.classList.remove('cursor-not-allowed');
            completeButton.disabled = false;
        }

        // Render the page
        const renderPage = (num) => {
            pageIsRendering = true;
            loadingIndicator.style.display = 'flex';
            
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
                    
                    // Update progress when page is loaded
                    updateProgress();
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
            updateButtonStates();
        };
        
        // Show next page
        const showNextPage = () => {
            if (currentPage >= totalPages) {
                return;
            }
            currentPage++;
            queueRenderPage(currentPage);
            updateButtonStates();
        };

        // Update button states based on current page
        function updateButtonStates() {
            prevPageButton.disabled = currentPage <= 1;
            nextPageButton.disabled = currentPage >= totalPages;
            
            // If we've reached the last page, ensure the complete button is enabled
            if (currentPage === totalPages) {
                visitedPages.add(currentPage);
                if (visitedPages.size === totalPages) {
                    enableCompleteButton();
                }
            }
        }
        
        // Get the PDF document
        pdfjsLib.getDocument(pdfUrl).promise.then(pdfDoc_ => {
            pdfDoc = pdfDoc_;
            totalPages = pdfDoc.numPages;
            
            // Update page count display
            pageCount.textContent = totalPages;
            
            // Initially disable complete button
            completeButton.classList.add('bg-gray-400');
            completeButton.classList.add('cursor-not-allowed');
            completeButton.disabled = true;
            
            // Special case: If this is a single page PDF, enable the complete button immediately
            if (totalPages === 1) {
                visitedPages.add(1);
                enableCompleteButton();
            }
            
            // Render first page
            renderPage(currentPage);
            updateButtonStates();
            
            // Setup keyboard shortcuts for easy navigation
            document.addEventListener('keydown', function(e) {
                if (e.key === 'ArrowLeft') {
                    showPrevPage();
                } else if (e.key === 'ArrowRight') {
                    showNextPage();
                }
            });
        }).catch(err => {
            // Display error
            loadingIndicator.innerHTML = `<p class="text-red-500">Error loading PDF: ${err.message}</p>`;
            console.error('Error loading PDF:', err);
        });
        
        // Button events
        prevPageButton.addEventListener('click', showPrevPage);
        nextPageButton.addEventListener('click', showNextPage);
        
        // Mark as complete
        completeButton.addEventListener('click', function(e) {
            e.preventDefault(); // Prevent default form submission
            
            // Show a loading state on the button
            completeButton.disabled = true;
            completeButton.innerText = '{{ __("Marking as Complete...") }}';
            
            // Get the form data and CSRF token
            const token = document.querySelector('input[name="_token"]').value;
            const formAction = completeForm.getAttribute('action');
            
            // Submit via AJAX
            fetch(formAction, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: '_token=' + encodeURIComponent(token),
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Redirect to the course page on success
                    window.location.href = '{{ route("candidate.courses.show", $course) }}';
                } else {
                    // If there was an error, re-enable the button
                    completeButton.disabled = false;
                    completeButton.innerText = '{{ __("Mark as Complete") }}';
                    alert('{{ __("Error marking material as complete. Please try again.") }}');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                completeButton.disabled = false;
                completeButton.innerText = '{{ __("Mark as Complete") }}';
                alert('{{ __("Error marking material as complete. Please try again.") }}');
            });
        });
        });
    </script>
@endsection 
@extends('layouts.main')

@section('content')
    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-semibold text-gray-800">
                        {{ __('Add Course Material') }}
                    </h2>
                    <a href="{{ route('inspector.courses.show', $course) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                        {{ __('Back to Course') }}
                    </a>
                </div>

                @if ($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form action="{{ route('inspector.courses.materials.store', $course) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="grid grid-cols-1 gap-6">
                        <!-- Title -->
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">{{ __('Title') }}</label>
                            <input type="text" name="title" id="title" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ old('title') }}" required>
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">{{ __('Description') }}</label>
                            <textarea name="description" id="description" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description') }}</textarea>
                        </div>

                        <!-- Material Type Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Material Type') }}</label>
                            <div class="flex space-x-6">
                                <div class="flex items-center">
                                    <input type="radio" name="material_type" id="type_pdf" value="pdf" class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500" checked>
                                    <label for="type_pdf" class="ml-2 block text-sm text-gray-700">{{ __('PDF Document') }}</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="radio" name="material_type" id="type_video" value="video" class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                                    <label for="type_video" class="ml-2 block text-sm text-gray-700">{{ __('YouTube Video') }}</label>
                                </div>
                            </div>
                        </div>

                        <!-- PDF File (shown when PDF type is selected) -->
                        <div id="pdf_section">
                            <div>
                                <label for="pdf_file" class="block text-sm font-medium text-gray-700">{{ __('PDF File') }}</label>
                                <div class="mt-1 flex items-center">
                                    <input type="file" name="pdf_file" id="pdf_file" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" accept="application/pdf">
                                </div>
                                <p class="mt-1 text-sm text-gray-500">{{ __('Maximum file size: 10MB') }}</p>
                            </div>

                            <!-- Thumbnail Image for PDF -->
                            <div class="mt-4">
                                <label for="thumbnail" class="block text-sm font-medium text-gray-700">{{ __('Thumbnail Image (Optional)') }}</label>
                                <div class="mt-1 flex items-center">
                                    <input type="file" name="thumbnail" id="thumbnail" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" accept="image/*">
                                </div>
                                <p class="mt-1 text-sm text-gray-500">{{ __('If not provided, a thumbnail will be generated from the first page of the PDF.') }}</p>
                            </div>
                        </div>

                        <!-- YouTube Video URL (shown when Video type is selected) -->
                        <div id="video_section" class="hidden">
                            <div>
                                <label for="video_url" class="block text-sm font-medium text-gray-700">{{ __('YouTube Video URL') }}</label>
                                <input type="url" name="video_url" id="video_url" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ old('video_url') }}" placeholder="https://www.youtube.com/watch?v=..." pattern="https?://(www\.)?(youtube\.com|youtu\.be)/.+">
                                <p class="mt-1 text-sm text-gray-500">{{ __('Enter the full YouTube video URL (e.g., https://www.youtube.com/watch?v=abcdefghijk)') }}</p>
                            </div>
                            
                            <!-- Thumbnail Image for Video (Optional) -->
                            <div class="mt-4">
                                <label for="video_thumbnail" class="block text-sm font-medium text-gray-700">{{ __('Custom Thumbnail (Optional)') }}</label>
                                <div class="mt-1 flex items-center">
                                    <input type="file" name="video_thumbnail" id="video_thumbnail" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" accept="image/*">
                                </div>
                                <p class="mt-1 text-sm text-gray-500">{{ __('If not provided, the YouTube thumbnail will be used.') }}</p>
                            </div>
                        </div>

                        <div class="flex justify-end pt-6">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                {{ __('Upload Material') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Toggle visibility of PDF and Video sections based on material type selection
        document.addEventListener('DOMContentLoaded', function() {
            const pdfRadio = document.getElementById('type_pdf');
            const videoRadio = document.getElementById('type_video');
            const pdfSection = document.getElementById('pdf_section');
            const videoSection = document.getElementById('video_section');
            const pdfFileInput = document.getElementById('pdf_file');
            const videoUrlInput = document.getElementById('video_url');

            function toggleSections() {
                if (pdfRadio.checked) {
                    pdfSection.classList.remove('hidden');
                    videoSection.classList.add('hidden');
                    pdfFileInput.setAttribute('required', 'required');
                    videoUrlInput.removeAttribute('required');
                } else {
                    pdfSection.classList.add('hidden');
                    videoSection.classList.remove('hidden');
                    pdfFileInput.removeAttribute('required');
                    videoUrlInput.setAttribute('required', 'required');
                }
            }

            // Initial toggle
            toggleSections();

            // Add event listeners
            pdfRadio.addEventListener('change', toggleSections);
            videoRadio.addEventListener('change', toggleSections);
            
            // Debug form submission
            const debugButton = document.getElementById('debug-form-button');
            debugButton.addEventListener('click', function() {
                const form = document.querySelector('form');
                const formData = new FormData(form);
                
                fetch('/debug-material-creation', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Debug data:', data);
                    alert('Debug information logged. Check console for details.');
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            });
        });
    </script>
@endsection 
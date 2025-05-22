@extends('layouts.main')

@section('content')
    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-semibold text-gray-800">
                        {{ __('Ajouter un matériel de cours') }}
                    </h2>
                    <a href="{{ route('inspector.courses.show', $course) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                        {{ __('Retour au cours') }}
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

                <form action="{{ route('inspector.courses.materials.store', $course) }}" method="POST" enctype="multipart/form-data" id="materialForm">
                    @csrf
                    
                    <!-- Hidden field for maximum file size -->
                    <input type="hidden" name="MAX_FILE_SIZE" value="41943040">
                    
                    @if ($errors->any())
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    
                    <div id="uploadError" class="hidden mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        <p id="errorMessage"></p>
                    </div>

                    <div class="grid grid-cols-1 gap-6">
                        <!-- Title -->
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">{{ __('Titre') }}</label>
                            <input type="text" name="title" id="title" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ old('title') }}" required>
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">{{ __('Description') }}</label>
                            <textarea name="description" id="description" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description') }}</textarea>
                        </div>

                        <!-- Material Type Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Type de matériel') }}</label>
                            <div class="flex space-x-6">
                                <div class="flex items-center">
                                    <input type="radio" name="material_type" id="type_pdf" value="pdf" class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500" checked>
                                    <label for="type_pdf" class="ml-2 block text-sm text-gray-700">{{ __('Document PDF') }}</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="radio" name="material_type" id="type_video" value="video" class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                                    <label for="type_video" class="ml-2 block text-sm text-gray-700">{{ __('Vidéo YouTube') }}</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="radio" name="material_type" id="type_audio" value="audio" class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                                    <label for="type_audio" class="ml-2 block text-sm text-gray-700">{{ __('Fichier audio') }}</label>
                                </div>
                            </div>
                        </div>

                        <!-- PDF File (shown when PDF type is selected) -->
                        <div id="pdf_section">
                            <div>
                                <label for="pdf_file" class="block text-sm font-medium text-gray-700">{{ __('Fichier PDF') }}</label>
                                <div class="mt-1 flex items-center">
                                    <input type="file" name="pdf_file" id="pdf_file" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" accept="application/pdf">
                                </div>
                                <p class="mt-1 text-sm text-gray-500">{{ __('Taille maximale: 10MB') }}</p>
                            </div>

                            <!-- Thumbnail Image for PDF -->
                            <div class="mt-4">
                                <label for="thumbnail" class="block text-sm font-medium text-gray-700">{{ __('Image miniature (Optionnel)') }}</label>
                                <div class="mt-1 flex items-center">
                                    <input type="file" name="thumbnail" id="thumbnail" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" accept="image/*">
                                </div>
                                <p class="mt-1 text-sm text-gray-500">{{ __('Si non fourni, une image miniature sera générée à partir de la première page du PDF.') }}</p>
                            </div>
                        </div>

                        <!-- YouTube Video URL (shown when Video type is selected) -->
                        <div id="video_section" class="hidden">
                            <div>
                                <label for="video_url" class="block text-sm font-medium text-gray-700">{{ __('URL de la vidéo YouTube') }}</label>
                                <input type="url" name="video_url" id="video_url" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ old('video_url') }}" placeholder="https://www.youtube.com/watch?v=..." pattern="https?://(www\.)?(youtube\.com|youtu\.be)/.+">
                                <p class="mt-1 text-sm text-gray-500">{{ __('Entrez l\'URL complète de la vidéo YouTube (e.g., https://www.youtube.com/watch?v=abcdefghijk)') }}</p>
                            </div>
                            
                            <!-- Thumbnail Image for Video (Optional) -->
                            <div class="mt-4">
                                <label for="video_thumbnail" class="block text-sm font-medium text-gray-700">{{ __('Image miniature personnalisée (Optionnel)') }}</label>
                                <div class="mt-1 flex items-center">
                                    <input type="file" name="video_thumbnail" id="video_thumbnail" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" accept="image/*">
                                </div>
                                <p class="mt-1 text-sm text-gray-500">{{ __('Si non fourni, l\'image miniature de YouTube sera utilisée.') }}</p>
                            </div>
                        </div>

                        <!-- Audio File (shown when Audio type is selected) -->
                        <div id="audio_section" class="hidden">
                            <div>
                                <label for="audio_file" class="block text-sm font-medium text-gray-700">{{ __('Fichier audio') }} <span class="text-green-600 font-bold">{{ __('Taille maximale: 40MB') }}</span></label>
                                <div class="mt-1 flex items-center">
                                    <input type="file" name="audio_file" id="audio_file" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" accept="audio/*">
                                </div>
                                <p class="mt-1 text-sm text-gray-500">{{ __('Formats acceptés: MP3, WAV, OGG, etc. Taille maximale: 40MB.') }}</p>
                                @error('audio_file')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Upload size warning -->
                            <div id="uploadError" class="mt-4 p-3 bg-red-100 text-red-700 border border-red-400 rounded-md hidden">
                                <p id="errorMessage" class="text-sm font-medium"></p>
                            </div>

                            <!-- Thumbnail Image for Audio -->
                            <div class="mt-4">
                                <label for="audio_thumbnail" class="block text-sm font-medium text-gray-700">{{ __('Image miniature (Optionnel)') }}</label>
                                <div class="mt-1 flex items-center">
                                    <input type="file" name="audio_thumbnail" id="audio_thumbnail" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" accept="image/*">
                                </div>
                                <p class="mt-1 text-sm text-gray-500">{{ __('Si non fourni, une image miniature par défaut sera utilisée.') }}</p>
                            </div>
                        </div>

                        <div class="flex justify-end pt-6">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                {{ __('Télécharger le matériel') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Toggle visibility of PDF, Video, and Audio sections based on material type selection
        document.addEventListener('DOMContentLoaded', function() {
            const pdfRadio = document.getElementById('type_pdf');
            const videoRadio = document.getElementById('type_video');
            const audioRadio = document.getElementById('type_audio');
            const pdfSection = document.getElementById('pdf_section');
            const videoSection = document.getElementById('video_section');
            const audioSection = document.getElementById('audio_section');
            const pdfFileInput = document.getElementById('pdf_file');
            const videoUrlInput = document.getElementById('video_url');
            const audioFileInput = document.getElementById('audio_file');
            const form = document.getElementById('materialForm');
            const uploadError = document.getElementById('uploadError');
            const errorMessage = document.getElementById('errorMessage');

            function toggleSections() {
                if (pdfRadio.checked) {
                    pdfSection.classList.remove('hidden');
                    videoSection.classList.add('hidden');
                    audioSection.classList.add('hidden');
                    pdfFileInput.setAttribute('required', 'required');
                    videoUrlInput.removeAttribute('required');
                    audioFileInput.removeAttribute('required');
                } else if (videoRadio.checked) {
                    pdfSection.classList.add('hidden');
                    videoSection.classList.remove('hidden');
                    audioSection.classList.add('hidden');
                    pdfFileInput.removeAttribute('required');
                    videoUrlInput.setAttribute('required', 'required');
                    audioFileInput.removeAttribute('required');
                } else if (audioRadio.checked) {
                    pdfSection.classList.add('hidden');
                    videoSection.classList.add('hidden');
                    audioSection.classList.remove('hidden');
                    pdfFileInput.removeAttribute('required');
                    videoUrlInput.removeAttribute('required');
                    audioFileInput.setAttribute('required', 'required');
                }
            }

            // Check file size before submission
            form.addEventListener('submit', function(e) {
                if (audioRadio.checked && audioFileInput.files.length > 0) {
                    const file = audioFileInput.files[0];
                    console.log('Audio file selected:', file.name, 'Size:', file.size);
                    
                    // Convert to MB for easier comparison (1MB = 1,048,576 bytes)
                    const fileSizeMB = file.size / 1048576;
                    
                    // Check against PHP's upload_max_filesize (40MB from our setting)
                    if (fileSizeMB > 40) {
                        e.preventDefault();
                        uploadError.classList.remove('hidden');
                        errorMessage.textContent = `Le fichier est trop lourd (${fileSizeMB.toFixed(2)}MB). La taille maximale autorisée est de 40MB. Veuillez choisir un fichier plus petit.`;
                        return false;
                    }
                }
            });

            // Initial toggle
            toggleSections();

            // Add event listeners
            pdfRadio.addEventListener('change', toggleSections);
            videoRadio.addEventListener('change', toggleSections);
            audioRadio.addEventListener('change', toggleSections);
            
            // Add file size validation for the audio input
            if (audioFileInput) {
                audioFileInput.addEventListener('change', function() {
                    if (this.files.length > 0) {
                        const file = this.files[0];
                        console.log('File selected:', file.name, 'Size:', file.size);
                        
                        // Convert to MB
                        const fileSizeMB = file.size / 1048576;
                        
                        // Show warning if file is too large
                        if (fileSizeMB > 40) {
                            uploadError.classList.remove('hidden');
                            errorMessage.textContent = `Attention: Le fichier est de ${fileSizeMB.toFixed(2)}MB. La taille maximale autorisée est de 40MB. Cette téléchargement risque de ne pas fonctionner.`;
                        } else {
                            uploadError.classList.add('hidden');
                        }
                    }
                });
            }
        });
    </script>
@endsection 
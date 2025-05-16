@extends('layouts.main')

@section('content')
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="mb-6">
                        <h2 class="text-2xl font-semibold text-gray-800">Edit Course Material: {{ $material->title }}</h2>
                        <p class="text-gray-600">Course: {{ $course->title }}</p>
                    </div>

                    @if($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <strong class="font-bold">Error!</strong>
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(!class_exists('Imagick'))
                        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4" role="alert">
                            <p class="font-bold">Note:</p>
                            <p>The ImageMagick library is not installed on this server. PDF thumbnails will use a default image and page count information may not be available. You can upload a custom thumbnail below.</p>
                        </div>
                    @endif

                    <form action="{{ route('inspector.courses.materials.update', [$course, $material]) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                            <input type="text" name="title" id="title" value="{{ old('title', $material->title) }}" class="mt-1 focus:ring-yellow-500 focus:border-yellow-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>

                        <div class="mb-4">
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea name="description" id="description" rows="4" class="mt-1 focus:ring-yellow-500 focus:border-yellow-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ old('description', $material->description) }}</textarea>
                        </div>

                        <div class="mb-4">
                            <label for="sequence_order" class="block text-sm font-medium text-gray-700">Sequence Order</label>
                            <input type="number" name="sequence_order" id="sequence_order" value="{{ old('sequence_order', $material->sequence_order) }}" min="1" class="mt-1 focus:ring-yellow-500 focus:border-yellow-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            <p class="mt-1 text-sm text-gray-500">Order in which this material appears in the course</p>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Current PDF</label>
                            <div class="mt-1 flex items-center">
                                <div class="flex items-center space-x-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-sm">{{ basename($material->content_path_or_url) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="pdf_file" class="block text-sm font-medium text-gray-700">Replace PDF File (Optional)</label>
                            <input type="file" name="pdf_file" id="pdf_file" accept="application/pdf" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-yellow-50 file:text-gray-700 hover:file:bg-yellow-100">
                            <p class="mt-1 text-sm text-gray-500">Upload a new PDF file to replace the current one (max. 10MB)</p>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700">Current Thumbnail</label>
                            <div class="mt-1">
                                @if($material->thumbnail_url)
                                    <img src="{{ $material->thumbnail_url }}" alt="{{ $material->title }}" class="h-32 w-32 object-cover rounded border border-gray-200">
                                @else
                                    <div class="h-32 w-32 flex items-center justify-center bg-gray-100 text-gray-400 rounded border border-gray-200">
                                        No thumbnail
                                    </div>
                                @endif
                            </div>
                            
                            <label for="thumbnail" class="block text-sm font-medium text-gray-700 mt-4">Replace Thumbnail (Optional)</label>
                            <input type="file" name="thumbnail" id="thumbnail" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-yellow-50 file:text-gray-700 hover:file:bg-yellow-100">
                            <p class="mt-1 text-sm text-gray-500">Upload a new image to replace the current thumbnail</p>
                        </div>

                        <div class="flex items-center">
                            <button type="submit" class="px-4 py-2 bg-yellow-500 text-gray-900 rounded hover:bg-yellow-400 active:bg-yellow-600 font-semibold text-xs uppercase tracking-widest mr-2">
                                Update Material
                            </button>
                            <a href="{{ route('inspector.courses.show', $course) }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 text-xs font-semibold uppercase tracking-widest">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection 
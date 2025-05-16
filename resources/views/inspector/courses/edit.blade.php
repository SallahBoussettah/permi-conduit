@extends('layouts.main')

@section('content')
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-semibold text-gray-800">{{ __('Edit Course') }}</h2>
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

                <form action="{{ route('inspector.courses.update', $course) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Left Column -->
                        <div class="space-y-6">
                            <!-- Title -->
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700">{{ __('Title') }} <span class="text-red-500">*</span></label>
                                <input type="text" name="title" id="title" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ old('title', $course->title) }}" required>
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700">{{ __('Description') }}</label>
                                <textarea name="description" id="description" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $course->description) }}</textarea>
                            </div>

                            <!-- Exam Section -->
                            <div>
                                <label for="exam_section_id" class="block text-sm font-medium text-gray-700">{{ __('Exam Section (Optional)') }}</label>
                                <select name="exam_section_id" id="exam_section_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">{{ __('None') }}</option>
                                    @foreach($examSections as $id => $name)
                                    <option value="{{ $id }}" {{ (old('exam_section_id', $course->exam_section_id) == $id) ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-sm text-gray-500">{{ __('Associate this course with a specific exam section.') }}</p>
                            </div>
                            
                            <!-- Category -->
                            <div>
                                <label for="category_id" class="block text-sm font-medium text-gray-700">{{ __('Category (Optional)') }}</label>
                                <select name="category_id" id="category_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">{{ __('None') }}</option>
                                    @foreach($categories as $id => $name)
                                    <option value="{{ $id }}" {{ (old('category_id', $course->category_id) == $id) ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-sm text-gray-500">{{ __('Assign this course to a category.') }}</p>
                            </div>

                            <!-- Permit Category -->
                            <div>
                                <label for="permit_category_id" class="block text-sm font-medium text-gray-700">{{ __('Permit Category (Optional)') }}</label>
                                <select name="permit_category_id" id="permit_category_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">{{ __('None - Available to All') }}</option>
                                    @foreach($permitCategories as $id => $name)
                                    <option value="{{ $id }}" {{ (old('permit_category_id', $course->permit_category_id) == $id) ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-sm text-gray-500">{{ __('Assign this course to a specific permit category (C, CE, D, etc.). If selected, the course will ONLY be visible to candidates with this permit category. If not selected, the course will be available to all candidates.') }}</p>
                            </div>
                        </div>
                        
                        <!-- Right Column -->
                        <div class="space-y-6">
                            <!-- Thumbnail -->
                            <div>
                                <label for="thumbnail" class="block text-sm font-medium text-gray-700">{{ __('Thumbnail Image') }}</label>
                                <div class="mt-1 flex items-center">
                                    <div class="w-full">
                                        <div id="thumbnail-preview" class="{{ $course->thumbnail ? '' : 'hidden' }} mb-4">
                                            <img src="{{ $course->thumbnail ? asset('storage/' . $course->thumbnail) : '#' }}" alt="Thumbnail Preview" class="w-40 h-auto object-cover rounded-md border border-gray-300">
                                        </div>
                                        <div class="flex items-center justify-center w-full">
                                            <label for="thumbnail" class="flex flex-col items-center justify-center w-full h-40 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100">
                                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                                    <svg class="w-10 h-10 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                                    </svg>
                                                    <p class="mb-2 text-sm text-gray-500">
                                                    @if($course->thumbnail)
                                                        <span class="font-semibold">{{ __('Change image') }}</span>
                                                    @else
                                                        <span class="font-semibold">{{ __('Click to upload') }}</span> {{ __('or drag and drop') }}
                                                    @endif
                                                    </p>
                                                    <p class="text-xs text-gray-500">{{ __('PNG, JPG or JPEG (MAX. 2MB)') }}</p>
                                                </div>
                                                <input id="thumbnail" name="thumbnail" type="file" class="hidden" accept="image/png,image/jpeg,image/jpg" onchange="previewImage(event)"/>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <p class="mt-1 text-sm text-gray-500">{{ __('Upload a thumbnail image for the course. It will be displayed on the candidate\'s course listing page.') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between pt-6">
                        <div>
                            <!-- Empty div for spacing -->
                        </div>
                        <div>
                            <!-- Save button -->
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                {{ __('Save Changes') }}
                            </button>
                        </div>
                    </div>
                </form>
                
                <!-- Delete form moved outside of the main form -->
                <div class="mt-4">
                    <form action="{{ route('inspector.courses.destroy', $course) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this course? All associated materials will also be deleted.') }}');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:border-red-700 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150">
                            {{ __('Delete Course') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function previewImage(event) {
            const input = event.target;
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const preview = document.getElementById('thumbnail-preview');
                    preview.classList.remove('hidden');
                    preview.querySelector('img').src = e.target.result;
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
@endsection 
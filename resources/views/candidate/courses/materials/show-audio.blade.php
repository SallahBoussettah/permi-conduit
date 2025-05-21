@extends('layouts.main')

@section('content')
<style>
    /* Custom styles for audio player */
    #audio-container {
        max-width: 900px;
        margin: 0 auto;
    }
    .audio-player {
        background-color: #f3f4f6;
        border-radius: 0.5rem;
        padding: 1rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    .audio-player audio {
        width: 100%;
    }
    .audio-controls {
        display: flex;
        align-items: center;
        margin-top: 1rem;
    }
    .play-pause-btn {
        background-color: #eab308;
        color: #1f2937;
        border: none;
        border-radius: 50%;
        width: 3rem;
        height: 3rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        margin-right: 1rem;
    }
    .play-pause-btn:hover {
        background-color: #f59e0b;
    }
    .audio-progress {
        flex-grow: 1;
    }
    .audio-progress-container {
        width: 100%;
        height: 6px;
        background-color: #d1d5db;
        border-radius: 3px;
        position: relative;
        cursor: pointer;
    }
    .audio-progress-bar {
        position: absolute;
        height: 100%;
        background-color: #eab308;
        border-radius: 3px;
        width: 0;
    }
    .audio-time {
        display: flex;
        justify-content: space-between;
        font-size: 0.875rem;
        color: #6b7280;
        margin-top: 0.5rem;
    }
</style>
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-semibold text-gray-800">{{ $material->title }}</h2>
                <div class="flex space-x-2">
                    <a href="{{ route('candidate.courses.show', $course) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                        {{ __('Back to Course') }}
                    </a>
                    <button id="mark-complete-btn" class="inline-flex items-center px-4 py-2 bg-yellow-500 text-gray-900 border border-transparent rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-yellow-400 active:bg-yellow-600 focus:outline-none focus:border-yellow-700 focus:ring ring-yellow-300 disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-150 {{ $progress->completed ? 'bg-yellow-400' : '' }}">
                        {{ $progress->completed ? __('Completed') : __('Mark as Complete') }}
                    </button>
                </div>
            </div>

            @if ($material->description)
            <div class="mb-6 bg-gray-50 p-4 rounded-md">
                <p class="text-gray-700">{{ $material->description }}</p>
            </div>
            @endif

            <div class="border rounded-lg overflow-hidden bg-white p-6">
                <div id="audio-container" class="relative rounded-lg overflow-hidden shadow-lg">
                    <div class="audio-player">
                        <!-- Native audio element (hidden) -->
                        <audio id="audio-element" class="hidden" preload="metadata">
                            <source src="{{ route('candidate.courses.materials.audio', ['course' => $course, 'material' => $material]) }}" type="audio/mpeg">
                            {{ __('Your browser does not support the audio element.') }}
                        </audio>
                        
                        <!-- Custom audio player UI -->
                        <div class="audio-controls">
                            <button id="play-pause-btn" class="play-pause-btn">
                                <svg id="play-icon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <svg id="pause-icon" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </button>
                            
                            <div class="audio-progress">
                                <div id="audio-progress-container" class="audio-progress-container">
                                    <div id="audio-progress-bar" class="audio-progress-bar"></div>
                                </div>
                                <div class="audio-time">
                                    <span id="current-time">00:00</span>
                                    <span id="duration">00:00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-6">
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div id="progress-bar" class="bg-yellow-500 h-2.5 rounded-full" style="width: {{ $progress->progress_percentage }}%"></div>
                </div>
                <div class="flex justify-between mt-2 text-sm text-gray-600">
                    <span>0%</span>
                    <span id="progress-percentage">{{ $progress->progress_percentage }}%</span>
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const audioElement = document.getElementById('audio-element');
        const playPauseBtn = document.getElementById('play-pause-btn');
        const playIcon = document.getElementById('play-icon');
        const pauseIcon = document.getElementById('pause-icon');
        const progressBar = document.getElementById('audio-progress-bar');
        const progressContainer = document.getElementById('audio-progress-container');
        const currentTimeEl = document.getElementById('current-time');
        const durationEl = document.getElementById('duration');
        const courseProgressBar = document.getElementById('progress-bar');
        const progressPercentage = document.getElementById('progress-percentage');
        const completeButton = document.getElementById('mark-complete-btn');
        const completeForm = document.getElementById('complete-form');
        
        let isPlaying = false;
        let progress = {{ $progress->progress_percentage }};
        let checkpoints = {};
        let isCompleted = {{ $progress->completed ? 'true' : 'false' }};
        
        // If already completed, enable complete button
        if (isCompleted) {
            enableCompleteButton();
        } else {
            completeButton.disabled = true;
            completeButton.classList.add('bg-gray-400');
            completeButton.classList.add('cursor-not-allowed');
        }
        
        // Format time in MM:SS
        function formatTime(seconds) {
            const mins = Math.floor(seconds / 60);
            const secs = Math.floor(seconds % 60);
            return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        }
        
        // Update audio progress display
        function updateProgress() {
            if (audioElement.duration) {
                const percent = (audioElement.currentTime / audioElement.duration) * 100;
                progressBar.style.width = `${percent}%`;
                
                // Update time displays
                currentTimeEl.textContent = formatTime(audioElement.currentTime);
                
                // Track progress in 10% intervals
                const progressSegment = Math.floor(percent / 10);
                if (!checkpoints[progressSegment] && progressSegment >= 0 && progressSegment <= 10) {
                    checkpoints[progressSegment] = true;
                    
                    // Calculate overall progress based on unique segments listened to
                    const uniqueSegments = Object.keys(checkpoints).length;
                    const newProgress = Math.min(Math.floor((uniqueSegments / 11) * 100), 100);
                    
                    if (newProgress > progress) {
                        progress = newProgress;
                        updateCourseProgress(progress);
                    }
                    
                    // Enable complete button when progress is sufficient (75%)
                    if (newProgress >= 75) {
                        enableCompleteButton();
                    }
                }
            }
        }
        
        // Update course progress display and send to server
        function updateCourseProgress(newProgress) {
            courseProgressBar.style.width = `${newProgress}%`;
            progressPercentage.textContent = `${newProgress}%`;
            
            // Send progress update to server
            fetch('{{ route("candidate.courses.materials.progress", ["course" => $course, "material" => $material]) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    progress_percentage: newProgress
                })
            });
        }
        
        // Enable the complete button
        function enableCompleteButton() {
            completeButton.classList.remove('bg-gray-400');
            completeButton.classList.remove('cursor-not-allowed');
            completeButton.disabled = false;
        }
        
        // Audio event listeners
        audioElement.addEventListener('loadedmetadata', function() {
            durationEl.textContent = formatTime(audioElement.duration);
        });
        
        audioElement.addEventListener('timeupdate', updateProgress);
        
        audioElement.addEventListener('ended', function() {
            playIcon.classList.remove('hidden');
            pauseIcon.classList.add('hidden');
            isPlaying = false;
            
            // Mark as complete when finished
            progress = 100;
            updateCourseProgress(progress);
            enableCompleteButton();
        });
        
        // Play/Pause button
        playPauseBtn.addEventListener('click', function() {
            if (isPlaying) {
                audioElement.pause();
                playIcon.classList.remove('hidden');
                pauseIcon.classList.add('hidden');
            } else {
                audioElement.play();
                playIcon.classList.add('hidden');
                pauseIcon.classList.remove('hidden');
            }
            isPlaying = !isPlaying;
        });
        
        // Allow clicking on progress bar to seek
        progressContainer.addEventListener('click', function(e) {
            const rect = progressContainer.getBoundingClientRect();
            const pos = (e.clientX - rect.left) / rect.width;
            audioElement.currentTime = pos * audioElement.duration;
            updateProgress();
        });
        
        // Handle complete button click
        completeButton.addEventListener('click', function() {
            completeForm.submit();
        });
    });
</script>
@endsection 
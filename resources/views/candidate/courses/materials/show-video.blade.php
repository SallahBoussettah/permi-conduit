@extends('layouts.main')

@section('content')
<style>
    /* Custom styles for YouTube video player */
    #video-container {
        max-width: 900px;
        margin: 0 auto;
    }
    #youtube-player {
        height: 100%;
        width: 100%;
        min-height: 480px;
    }
    @media (max-width: 768px) {
        #youtube-player {
            min-height: 320px;
        }
    }
</style>
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-semibold text-gray-800">{{ $material->title }}</h2>
                <div class="flex space-x-2">
                    <a href="{{ route('candidate.courses.show', $course) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                        {{ __('Retour à la formation') }}
                    </a>
                    <button id="mark-complete-btn" class="inline-flex items-center px-4 py-2 bg-yellow-500 text-gray-900 border border-transparent rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-yellow-400 active:bg-yellow-600 focus:outline-none focus:border-yellow-700 focus:ring ring-yellow-300 disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-150 {{ $progress->completed ? 'bg-yellow-400' : '' }}">
                        {{ $progress->completed ? __('Complété') : __('Marquer comme complété') }}
                    </button>
                </div>
            </div>

            @if ($material->description)
            <div class="mb-6 bg-gray-50 p-4 rounded-md">
                <p class="text-gray-700">{{ $material->description }}</p>
            </div>
            @endif

            <div class="border rounded-lg overflow-hidden bg-gray-900">
                <div id="video-container" class="relative bg-black rounded-lg overflow-hidden shadow-lg">
                    <div id="youtube-player"></div>
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
                        {{ __('Matériel précédent') }}
                    </a>
                    @endif
                </div>
                <div>
                    @if ($nextMaterial)
                    <a href="{{ route('candidate.courses.materials.show', ['course' => $course, 'material' => $nextMaterial]) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                        {{ __('Matériel suivant') }}
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

<script src="https://www.youtube.com/iframe_api"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const completeButton = document.getElementById('mark-complete-btn');
        const completeForm = document.getElementById('complete-form');
        const progressBar = document.getElementById('progress-bar');
        const progressPercentage = document.getElementById('progress-percentage');

        let player;
        let videoId = '{{ $material->content_path_or_url }}';
        let isPlaying = false;
        let progress = {{ $progress->progress_percentage }};
        let trackingInterval;
        let duration = 0;
        let checkpoints = {}; // Track watched sections
        
        // YouTube Player API is ready
        window.onYouTubeIframeAPIReady = function() {
            player = new YT.Player('youtube-player', {
                videoId: videoId,
                playerVars: {
                    'autoplay': 0,
                    'modestbranding': 1,
                    'rel': 0,
                    'width': '100%',
                    'height': '100%'
                },
                events: {
                    'onReady': onPlayerReady,
                    'onStateChange': onPlayerStateChange
                }
            });
        };

        // Player is ready
        function onPlayerReady(event) {
            // Initialize duration once player is ready
            duration = player.getDuration();
            
            // Initialize progress tracking
            startProgressTracking();
        }

        // Player state has changed
        function onPlayerStateChange(event) {
            // If video starts playing
            if (event.data === YT.PlayerState.PLAYING) {
                isPlaying = true;
            } else {
                isPlaying = false;
            }
            
            // If video ends, mark as complete if we reached sufficient progress
            if (event.data === YT.PlayerState.ENDED) {
                updateProgress(100);
                enableCompleteButton();
            }
        }

        // Start tracking progress
        function startProgressTracking() {
            // Clear any existing interval
            if (trackingInterval) clearInterval(trackingInterval);
            
            // Set up tracking interval every second
            trackingInterval = setInterval(function() {
                if (isPlaying && duration > 0) {
                    // Get current time
                    const currentTime = player.getCurrentTime();
                    
                    // Mark 5-second segment as watched
                    const segment = Math.floor(currentTime / 5);
                    checkpoints[segment] = true;
                    
                    // Count watched segments
                    const totalSegments = Math.ceil(duration / 5);
                    const watchedSegments = Object.keys(checkpoints).length;
                    
                    // Calculate percentage
                    const newProgress = Math.min(Math.floor((watchedSegments / totalSegments) * 100), 100);
                    
                    // Update if progress increased
                    if (newProgress > progress) {
                        updateProgress(newProgress);
                    }
                    
                    // Enable complete button if significant progress has been made (75%)
                    if (newProgress >= 75) {
                        enableCompleteButton();
                    }
                }
            }, 1000);
        }

        // Update progress display and send to server
        function updateProgress(newProgress) {
            progress = newProgress;
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
                    progress_percentage: progress
                })
            });
        }
        
        // Enable the complete button
        function enableCompleteButton() {
            completeButton.classList.remove('bg-gray-400');
            completeButton.classList.remove('cursor-not-allowed');
            completeButton.disabled = false;
        }
        
        // Handle complete button click
        completeButton.addEventListener('click', function() {
            completeForm.submit();
        });
        
        // Clean up on page unload
        window.addEventListener('beforeunload', function() {
            if (trackingInterval) clearInterval(trackingInterval);
        });
    });
</script>
@endsection 
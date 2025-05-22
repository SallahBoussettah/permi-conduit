@extends('layouts.main')

@section('content')
<div class="bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center">
                <a href="{{ route('inspector.qcm-papers.show', $qcmPaper) }}" class="mr-4 text-indigo-600 hover:text-indigo-900">
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <h1 class="text-3xl font-bold text-gray-900">{{ __('Modifier la question') }}</h1>
            </div>
            <p class="mt-2 text-sm text-gray-700">{{ __('Mettre à jour la question pour le QCM:') }} {{ $qcmPaper->title }}</p>
        </div>

        <!-- QCM Grading Information -->
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">{{ __('Informations du QCM') }}</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <p>{{ __('Chaque QCM doit contenir exactement 10 questions.') }}</p>
                        <p class="mt-1">{{ __('Temps limite pour les candidats: 6 minutes') }}</p>
                        <p class="mt-1">{{ __('Échelle de notation:') }}</p>
                        <ul class="list-disc pl-5 space-y-1 mt-1">
                            <li>{{ __('9-10 réponses correctes: 3 points') }}</li>
                            <li>{{ __('7-8 réponses correctes: 2 points') }}</li>
                            <li>{{ __('6 réponses correctes: 1 point') }}</li>
                            <li>{{ __('5 réponses correctes: 0 point') }}</li>
                            <li>{{ __('Moins de 5 réponses correctes: Eliminatoire') }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <form action="{{ route('inspector.qcm-papers.questions.update', ['qcmPaper' => $qcmPaper->id, 'question' => $question->id]) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="space-y-6">
                        <!-- Question Text -->
                        <div>
                            <label for="question_text" class="block text-sm font-medium text-gray-700">{{ __('Texte de la question') }} <span class="text-red-500">*</span></label>
                            <div class="mt-1">
                                <textarea name="question_text" id="question_text" rows="3" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md @error('question_text') border-red-300 text-red-900 placeholder-red-300 focus:outline-none focus:ring-red-500 focus:border-red-500 @enderror" required>{{ old('question_text', $question->question_text) }}</textarea>
                            </div>
                            @error('question_text')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Question Type -->
                        <div>
                            <label for="question_type" class="block text-sm font-medium text-gray-700">{{ __('Type de question') }} <span class="text-red-500">*</span></label>
                            <div class="mt-1">
                                <select id="question_type" name="question_type" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md @error('question_type') border-red-300 text-red-900 placeholder-red-300 focus:outline-none focus:ring-red-500 focus:border-red-500 @enderror" required>
                                    <option value="multiple_choice" {{ old('question_type', $question->question_type) == 'multiple_choice' ? 'selected' : '' }}>{{ __('Choix multiple') }}</option>
                                    <option value="yes_no" {{ old('question_type', $question->question_type) == 'yes_no' ? 'selected' : '' }}>{{ __('Oui/Non') }}</option>
                                </select>
                            </div>
                            @error('question_type')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Sequence Number (Hidden) -->
                        <input type="hidden" name="sequence_number" value="{{ $question->sequence_number }}">

                        <!-- Image Upload -->
                        <div>
                            <label for="image" class="block text-sm font-medium text-gray-700">{{ __('Image (Optionnel)') }}</label>
                            @if($question->image_path)
                                <div class="mt-2">
                                    <img src="{{ asset('storage/' . $question->image_path) }}" alt="Question Image" class="max-h-40 rounded-md">
                                    <div class="mt-2">
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" name="remove_image" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            <span class="ml-2 text-sm text-gray-600">{{ __('Supprimer l\'image actuelle') }}</span>
                                        </label>
                                    </div>
                                </div>
                            @endif
                            <div class="mt-1">
                                <input type="file" name="image" id="image" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 @error('image') border-red-300 text-red-900 placeholder-red-300 focus:outline-none focus:ring-red-500 focus:border-red-500 @enderror">
                            </div>
                            @error('image')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-2 text-sm text-gray-500">{{ __('Télécharger une image optionnelle pour cette question.') }}</p>
                        </div>
                        
                        <!-- Explanation -->
                        <div>
                            <label for="explanation" class="block text-sm font-medium text-gray-700">{{ __('Explication (Optionnel)') }}</label>
                            <div class="mt-1">
                                <textarea name="explanation" id="explanation" rows="3" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md @error('explanation') border-red-300 text-red-900 placeholder-red-300 focus:outline-none focus:ring-red-500 focus:border-red-500 @enderror">{{ old('explanation', $question->explanation) }}</textarea>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">{{ __('Fournir une explication pour la réponse correcte. Cela sera affiché aux candidats après avoir terminé l\'examen.') }}</p>
                            @error('explanation')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Answers -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">{{ __('Réponses') }}</h3>
                            <p class="mt-1 text-sm text-gray-500">{{ __('Ajouter au moins 2 réponses et marquer une comme correcte.') }}</p>
                            
                            <div id="answers-container" class="mt-4 space-y-4">
                                @foreach($question->answers as $index => $answer)
                                    <div class="answer-item p-4 border border-gray-200 rounded-md">
                                        <div class="flex items-center">
                                            <input type="radio" name="answers[{{ $index }}][is_correct]" value="1" class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500" {{ $answer->is_correct ? 'checked' : '' }}>
                                            <div class="ml-3 flex-grow">
                                                <input type="hidden" name="answers[{{ $index }}][id]" value="{{ $answer->id }}">
                                                <input type="text" name="answers[{{ $index }}][text]" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="{{ __('Texte de la réponse') }}" value="{{ $answer->answer_text }}" required>
                                            </div>
                                            @if($index > 1)
                                                <button type="button" class="remove-answer ml-3 text-red-600 hover:text-red-900">
                                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                            <div class="mt-4">
                                <button type="button" id="add-answer" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg class="-ml-0.5 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                    </svg>
                                    {{ __('Ajouter une réponse') }}
                                </button>
                            </div>
                            
                            @error('answers')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <a href="{{ route('inspector.qcm-papers.show', $qcmPaper) }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Annuler') }}
                        </a>
                        <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Mettre à jour') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const answersContainer = document.getElementById('answers-container');
        const addAnswerButton = document.getElementById('add-answer');
        let answerCount = {{ count($question->answers) }};
        
        addAnswerButton.addEventListener('click', function() {
            const newAnswer = document.createElement('div');
            newAnswer.className = 'answer-item p-4 border border-gray-200 rounded-md';
            newAnswer.innerHTML = `
                <div class="flex items-center">
                    <input type="radio" name="answers[${answerCount}][is_correct]" value="1" class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                    <div class="ml-3 flex-grow">
                        <input type="text" name="answers[${answerCount}][text]" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="{{ __('Texte de la réponse') }}" required>
                    </div>
                    <button type="button" class="remove-answer ml-3 text-red-600 hover:text-red-900">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            `;
            
            answersContainer.appendChild(newAnswer);
            answerCount++;
            
            // Add event listener to the new remove button
            newAnswer.querySelector('.remove-answer').addEventListener('click', function() {
                newAnswer.remove();
            });
        });
        
        // Add event listeners to existing remove buttons
        document.querySelectorAll('.remove-answer').forEach(button => {
            button.addEventListener('click', function() {
                button.closest('.answer-item').remove();
            });
        });
    });
</script>
@endpush
@endsection 
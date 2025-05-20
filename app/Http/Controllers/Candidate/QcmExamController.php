<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\QcmPaper;
use App\Models\QcmExam;
use App\Models\QcmExamAnswer;
use App\Models\QcmQuestion;
use App\Models\QcmAnswer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class QcmExamController extends Controller
{
    /**
     * Display a listing of the candidate's exams.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get recent exams taken by the user
        $recentExams = $user->qcmExams()
            ->with('paper.permitCategory')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        // Get available exams for the user
        $permitCategoryIds = $user->permitCategories->pluck('id');
        
        // Build the base query for available papers
        $baseQuery = QcmPaper::whereIn('permit_category_id', $permitCategoryIds)
            ->where('status', true)
            ->where(function($query) use ($user) {
                $query->whereNull('school_id')
                    ->orWhere('school_id', $user->school_id);
            });
            
        // Get the total count
        $paperCount = $baseQuery->count();
        
        // Select only ONE random exam
        if ($paperCount > 0) {
            // Get all IDs, randomize, and then fetch just 1 paper
            $allPaperIds = $baseQuery->pluck('id')->toArray();
            
            // Shuffle the array of IDs
            shuffle($allPaperIds);
            
            // Take just the first ID
            $selectedId = $allPaperIds[0];
            
            // Query that specific paper with its relationships
            $availableExam = QcmPaper::where('id', $selectedId)
                ->with('permitCategory', 'questions')
                ->first();
                
            // Convert to collection to maintain compatibility with existing view
            $availableExams = collect([$availableExam]);
            
            \Log::info("Loaded index view with 1 randomly selected available exam from {$paperCount} total and " . count($recentExams) . " recent exams");
        } else {
            // No papers available
            $availableExams = collect();
            \Log::info("No available exam papers for user {$user->id}");
        }
        
        return view('candidate.qcm-exams.index', compact('recentExams', 'availableExams'));
    }
    
    /**
     * Display a listing of available papers for the candidate.
     */
    public function available()
    {
        $user = Auth::user();
        $permitCategoryIds = $user->permitCategories->pluck('id');
        
        // Get permit categories for filtering
        $permitCategories = $user->permitCategories;
        
        // Filter by permit category if requested
        $permitCategoryFilter = request('permit_category');
        
        $query = QcmPaper::whereIn('permit_category_id', $permitCategoryIds)
            ->where('status', true)
            ->where(function($query) use ($user) {
                $query->whereNull('school_id')
                    ->orWhere('school_id', $user->school_id);
            });
            
        // Apply permit category filter if provided
        if ($permitCategoryFilter) {
            $query->where('permit_category_id', $permitCategoryFilter);
        }
        
        // Instead of paginating all results, randomly select a single paper
        // First check if there are any available papers
        $paperCount = $query->count();
        
        if ($paperCount > 0) {
            // Get a random paper with all necessary relationships
            $randomOffset = rand(0, $paperCount - 1);
            $availableExams = $query->with(['questions', 'permitCategory'])
                                    ->skip($randomOffset)
                                    ->take(1)
                                    ->get();
            
            // Log the random selection
            \Log::info("Randomly selected 1 exam paper from {$paperCount} available papers for user {$user->id}");
        } else {
            // No papers available
            $availableExams = collect();
            \Log::info("No exam papers available for user {$user->id}");
        }
        
        return view('candidate.qcm-exams.available', compact('availableExams', 'permitCategories'));
    }
    
    /**
     * Start a new exam.
     */
    public function start(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'paper_id' => 'required|exists:qcm_papers,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $user = Auth::user();
        $paperId = $request->paper_id;
        
        // Check if the paper exists and is active
        $paper = QcmPaper::where('id', $paperId)
            ->where('status', true)
            ->first();
        
        if (!$paper) {
            return redirect()->back()
                ->withErrors(['paper_id' => 'The selected paper is not available.'])
                ->withInput();
        }
        
        // Check if the user has access to this paper
        $permitCategoryIds = $user->permitCategories->pluck('id');
        if (!$permitCategoryIds->contains($paper->permit_category_id)) {
            return redirect()->back()
                ->withErrors(['paper_id' => 'You do not have access to this paper.'])
                ->withInput();
        }
        
        // Check if the user has an ongoing exam for this paper
        $ongoingExam = $user->qcmExams()
            ->where('qcm_paper_id', $paperId)
            ->where('status', 'in_progress')
            ->first();
        
        if ($ongoingExam) {
            return redirect()->route('candidate.qcm-exams.show', $ongoingExam);
        }
        
        // Get all questions from the paper (exactly 10)
        $questions = $paper->questions()->get();
        
        // Check if the paper has exactly 10 questions
        if ($questions->count() != 10) {
            return redirect()->back()
                ->withErrors(['paper_id' => 'This paper does not have exactly 10 questions as required.'])
                ->withInput();
        }
        
        DB::beginTransaction();
        
        try {
            // Use current timestamp for started_at to ensure consistency
            $now = now();
            
            // Create a new exam
            $exam = new QcmExam();
            $exam->user_id = $user->id;
            $exam->qcm_paper_id = $paperId;
            $exam->started_at = $now;
            $exam->expires_at = $now->copy()->addMinutes(6); // Exactly 6 minutes (360 seconds)
            $exam->total_questions = 10;
            $exam->status = 'in_progress';
            $exam->school_id = $user->school_id;
            $exam->save();
            
            \Log::info("Created new exam {$exam->id} with started_at: {$exam->started_at}, expires_at: {$exam->expires_at}");
            
            DB::commit();
            
            return redirect()->route('candidate.qcm-exams.show', $exam);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->withErrors(['error' => 'Failed to start exam: ' . $e->getMessage()])
                ->withInput();
        }
    }
    
    /**
     * Display the specified exam.
     */
    public function show(QcmExam $qcmExam)
    {
        $user = Auth::user();
        
        // Check if the exam belongs to the user
        if ($qcmExam->user_id != $user->id) {
            abort(403, 'Unauthorized action.');
        }
        
        // If the exam is completed or timed out, redirect to results
        if ($qcmExam->status === 'completed' || $qcmExam->status === 'timed_out') {
            return redirect()->route('candidate.qcm-exams.results', $qcmExam);
        }
        
        // Make sure expires_at is set, needed for the timer
        if (!$qcmExam->expires_at && $qcmExam->started_at) {
            $qcmExam->expires_at = Carbon::parse($qcmExam->started_at)->addMinutes(6); // 6 minutes (360 seconds)
            $qcmExam->save();
            \Log::info("Set expires_at for exam {$qcmExam->id} to {$qcmExam->expires_at}");
        }
        
        // Detailed timing information for debugging
        \Log::info("Exam {$qcmExam->id} - started_at: {$qcmExam->started_at}, expires_at: {$qcmExam->expires_at}, current time: " . now());
        
        // Check if the exam has timed out
        $remainingTime = $qcmExam->getRemainingTimeInSeconds();
        \Log::info("Calculated remaining time for exam {$qcmExam->id}: {$remainingTime} seconds");
        
        if ($remainingTime <= 0) {
            // Update the exam status to timed_out
            $qcmExam->status = 'timed_out';
            $qcmExam->completed_at = now();
            $qcmExam->duration_seconds = 360; // 6 minutes
            $qcmExam->save();
            
            // Process answers and calculate score
            $this->processFinalScore($qcmExam);
            
            return redirect()->route('candidate.qcm-exams.results', $qcmExam)
                ->with('warning', 'Your exam has timed out.');
        }
        
        // Get all 10 questions from the paper
        $questions = $qcmExam->paper->questions()
            ->with('answers')
            ->get();

        // Get all answers already provided by the candidate
        $rawExamAnswers = $qcmExam->answers()
            ->with(['question', 'selectedAnswer'])
            ->get();
            
        // Log for debugging
        \Log::info("Loaded exam {$qcmExam->id} with {$questions->count()} questions and {$rawExamAnswers->count()} answers");
            
        // Convert to a keyed array for easier access in the view
        $examAnswers = [];
        foreach ($rawExamAnswers as $answer) {
            $examAnswers[$answer->qcm_question_id] = $answer;
            \Log::info("Answer for question {$answer->qcm_question_id}: selected answer ID {$answer->qcm_answer_id}, is_correct: " . ($answer->is_correct ? 'true' : 'false'));
        }
            
        return view('candidate.qcm-exams.show', compact('qcmExam', 'questions', 'examAnswers', 'remainingTime'));
    }
    
    /**
     * Submit an answer for a question.
     */
    public function answer(Request $request, QcmExam $qcmExam)
    {
        $user = Auth::user();
        
        // Check if the exam belongs to the user
        if ($qcmExam->user_id != $user->id) {
            return response()->json(['error' => 'Unauthorized action.'], 403);
        }
        
        // Check if the exam is in progress
        if ($qcmExam->status !== 'in_progress') {
            return response()->json(['error' => 'This exam is not in progress.'], 400);
        }
        
        // Check if the exam has timed out
        $remainingTime = $qcmExam->getRemainingTimeInSeconds();
        if ($remainingTime <= 0) {
            // Update the exam status to timed_out
            $qcmExam->status = 'timed_out';
            $qcmExam->completed_at = now();
            $qcmExam->duration_seconds = 360; // 6 minutes
            $qcmExam->save();
            
            // Process answers and calculate score
            $this->processFinalScore($qcmExam);
            
            return response()->json(['error' => 'Your exam has timed out.', 'redirect' => route('candidate.qcm-exams.results', $qcmExam)], 400);
        }
        
        $validator = Validator::make($request->all(), [
            'question_id' => 'required|exists:qcm_questions,id',
            'answer_id' => 'required|exists:qcm_answers,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }
        
        // Check if the question belongs to the exam's paper
        $question = QcmQuestion::where('id', $request->question_id)
            ->where('qcm_paper_id', $qcmExam->qcm_paper_id)
            ->first();
        
        if (!$question) {
            return response()->json(['error' => 'This question does not belong to the exam.'], 400);
        }
        
        // Check if the answer belongs to the question
        $answer = QcmAnswer::where('id', $request->answer_id)
            ->where('qcm_question_id', $request->question_id)
            ->first();
        
        if (!$answer) {
            return response()->json(['error' => 'This answer does not belong to the question.'], 400);
        }
        
        // Log answer information
        \Log::info("Saving answer for exam {$qcmExam->id}, question {$request->question_id}, answer {$request->answer_id}, is_correct: " . ($answer->is_correct ? 'true' : 'false'));
        
        // Save the answer
        $examAnswer = QcmExamAnswer::updateOrCreate(
            [
                'qcm_exam_id' => $qcmExam->id,
                'qcm_question_id' => $request->question_id,
            ],
            [
                'qcm_answer_id' => $request->answer_id,
                'is_correct' => $answer->is_correct,
            ]
        );
        
        return response()->json(['success' => true, 'is_correct' => $answer->is_correct]);
    }
    
    /**
     * Submit the exam.
     */
    public function submit(Request $request, QcmExam $qcmExam)
    {
        $user = Auth::user();
        
        // Check if the exam belongs to the user
        if ($qcmExam->user_id != $user->id) {
            return redirect()->back()
                ->withErrors(['error' => 'Unauthorized action.']);
        }
        
        // Check if the exam is in progress
        if ($qcmExam->status !== 'in_progress') {
            return redirect()->route('candidate.qcm-exams.results', $qcmExam);
        }
        
        \Log::info("Submitting exam {$qcmExam->id} for user {$user->id}");
        
        // Process any submitted answers that weren't saved via AJAX
        if ($request->has('questions')) {
            foreach ($request->questions as $questionId => $data) {
                if (isset($data['answer_id'])) {
                    $answerId = $data['answer_id'];
                    $answer = QcmAnswer::find($answerId);
                    
                    if ($answer && $answer->qcm_question_id == $questionId) {
                        \Log::info("Processing final answer from form submit: question {$questionId}, answer {$answerId}, is_correct: " . ($answer->is_correct ? 'true' : 'false'));
                        
                        // Save the answer
                        QcmExamAnswer::updateOrCreate(
                            [
                                'qcm_exam_id' => $qcmExam->id,
                                'qcm_question_id' => $questionId,
                            ],
                            [
                                'qcm_answer_id' => $answerId,
                                'is_correct' => $answer->is_correct,
                            ]
                        );
                    }
                }
            }
        }
        
        // Calculate the duration - ensure it's a positive value
        $startedAt = Carbon::parse($qcmExam->started_at);
        $completedAt = now();
        
        // Make sure the completed time is after the start time
        if ($completedAt->lt($startedAt)) {
            $completedAt = $startedAt->copy()->addSeconds(1); // Ensure at least 1 second duration
            \Log::warning("Completion time was before start time for exam {$qcmExam->id}, adjusted to ensure positive duration");
        }
        
        // Calculate duration in seconds
        $durationSeconds = $startedAt->diffInSeconds($completedAt);
        
        // Cap duration at maximum allowed time (6 minutes = 360 seconds)
        if ($durationSeconds > 360) {
            $durationSeconds = 360;
            \Log::info("Capped duration for exam {$qcmExam->id} to maximum time (360 seconds)");
        }
        
        // Update the exam with completion details
        $qcmExam->status = 'completed';
        $qcmExam->completed_at = $completedAt;
        $qcmExam->duration_seconds = $durationSeconds;
        $qcmExam->save();
        
        \Log::info("Exam {$qcmExam->id} marked as completed, duration: {$durationSeconds} seconds");
        
        // Process answers and calculate score
        $this->processFinalScore($qcmExam);
        
        return redirect()->route('candidate.qcm-exams.results', $qcmExam);
    }
    
    /**
     * Calculate and save the final score for an exam.
     */
    private function processFinalScore(QcmExam $qcmExam)
    {
        // Calculate the score more reliably by directly querying the database
        $correctAnswersCount = DB::table('qcm_exam_answers')
            ->where('qcm_exam_id', $qcmExam->id)
            ->where('is_correct', true)
            ->count();
        
        // Get total answered questions
        $totalAnswered = DB::table('qcm_exam_answers')
            ->where('qcm_exam_id', $qcmExam->id)
            ->count();
            
        \Log::info("Calculating score for exam {$qcmExam->id}: {$correctAnswersCount} correct answers out of {$totalAnswered} answered questions");
        
        // Calculate points using the official grading scale:
        // 9-10 correct: 3 points
        // 7-8 correct: 2 points
        // 6 correct: 1 point
        // 5 or fewer correct: 0 points and eliminatory
        $pointsEarned = 0;
        $isEliminatory = false;
        
        if ($correctAnswersCount >= 9) {
            $pointsEarned = 3;
        } elseif ($correctAnswersCount >= 7) {
            $pointsEarned = 2;
        } elseif ($correctAnswersCount == 6) {
            $pointsEarned = 1;
        } else {
            $pointsEarned = 0;
            $isEliminatory = true;
        }
        
        \Log::info("Exam {$qcmExam->id} earned {$pointsEarned} points, eliminatory: " . ($isEliminatory ? 'yes' : 'no'));
        
        // Update the exam with the final score
        $qcmExam->correct_answers_count = $correctAnswersCount;
        $qcmExam->points_earned = $pointsEarned;
        $qcmExam->is_eliminatory = $isEliminatory;
        $qcmExam->save();
        
        return $qcmExam;
    }
    
    /**
     * Display the results of the exam.
     */
    public function results(QcmExam $qcmExam)
    {
        $user = Auth::user();
        
        // Check if the exam belongs to the user
        if ($qcmExam->user_id != $user->id) {
            abort(403, 'Unauthorized action.');
        }
        
        // Check if the exam is completed or timed out
        if ($qcmExam->status === 'in_progress') {
            return redirect()->route('candidate.qcm-exams.show', $qcmExam);
        }
        
        // Get the answers provided by the candidate
        $examAnswers = $qcmExam->answers()
            ->with('question.answers', 'selectedAnswer')
            ->get();
        
        // Calculate the percentage
        $percentage = ($qcmExam->correct_answers_count / $qcmExam->total_questions) * 100;
        
        return view('candidate.qcm-exams.results', compact('qcmExam', 'examAnswers', 'percentage'));
    }
}

<?php

namespace App\Http\Controllers\Inspector;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\QcmPaper;
use App\Models\QcmQuestion;
use App\Models\QcmAnswer;
use App\Models\QcmSection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Csv\Reader;
use League\Csv\Writer;
use Illuminate\Support\Facades\Schema;

class QcmQuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(QcmPaper $qcmPaper)
    {
        $this->authorize('view', $qcmPaper);
        
        // Log paper ID for debugging
        \Log::info('Viewing questions for QCM Paper ID: ' . $qcmPaper->id);
        
        // Check if section_id column exists in qcm_questions table
        if (Schema::hasColumn('qcm_questions', 'section_id')) {
            // Get all sections for this paper
            $sections = $qcmPaper->sections()->with(['questions' => function($query) {
                $query->orderBy('sequence_number', 'asc');
            }])->orderBy('sequence_number', 'asc')->get();
            
            // Log sections and question counts
            \Log::info('Found ' . $sections->count() . ' sections for paper ID: ' . $qcmPaper->id);
            foreach ($sections as $section) {
                \Log::info('Section ID: ' . $section->id . ', Title: ' . $section->title . ', Questions: ' . $section->questions->count());
            }
            
            // Get all questions for this paper to verify
            $totalQuestions = $qcmPaper->questions()->count();
            \Log::info('Total questions for paper ID: ' . $qcmPaper->id . ' = ' . $totalQuestions);
            
            // If no questions in any sections but paper has questions, they might not be properly associated with sections
            $questionsInSections = 0;
            foreach ($sections as $section) {
                $questionsInSections += $section->questions->count();
            }
            
            if ($questionsInSections === 0 && $totalQuestions > 0) {
                \Log::warning('Paper has questions but none are associated with sections. Fixing...');
                
                // Get the first section or create one
                $firstSection = $sections->first();
                if (!$firstSection) {
                    $firstSection = new QcmSection();
                    $firstSection->qcm_paper_id = $qcmPaper->id;
                    $firstSection->title = 'Default Section';
                    $firstSection->description = 'Default section for questions';
                    $firstSection->sequence_number = 1;
                    $firstSection->save();
                    $sections = collect([$firstSection]);
                }
                
                // Associate all questions with the first section
                $qcmPaper->questions()->update(['section_id' => $firstSection->id]);
                
                // Refresh sections with updated questions
                $sections = $qcmPaper->sections()->with(['questions' => function($query) {
                    $query->orderBy('sequence_number', 'asc');
                }])->orderBy('sequence_number', 'asc')->get();
                
                \Log::info('Fixed question-section associations for paper ID: ' . $qcmPaper->id);
            }
            
            return view('inspector.qcm-questions.index', compact('qcmPaper', 'sections'));
        } else {
            // If section_id column doesn't exist, just get all questions directly
            $questions = $qcmPaper->questions()->orderBy('sequence_number', 'asc')->get();
            
            // Log questions found
            \Log::info('Section_id column not found. Using ' . $questions->count() . ' direct questions for paper ID: ' . $qcmPaper->id);
            
            // Create a default section to display all questions
            $defaultSection = new \stdClass();
            $defaultSection->id = 0;
            $defaultSection->title = 'All Questions';
            $defaultSection->description = 'All questions for this paper';
            $defaultSection->questions = $questions;
            
            $sections = collect([$defaultSection]);
            
            return view('inspector.qcm-questions.index', compact('qcmPaper', 'sections'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(QcmPaper $qcmPaper)
    {
        $this->authorize('update', $qcmPaper);
        
        // Get the highest sequence number
        $maxSequence = $qcmPaper->questions()->max('sequence_number') ?? 0;
        
        // Get sections for the dropdown
        $sections = $qcmPaper->sections()->orderBy('sequence_number')->get();
        
        // If no sections, create a default one
        if ($sections->isEmpty()) {
            $section = new QcmSection();
            $section->qcm_paper_id = $qcmPaper->id;
            $section->title = 'Default Section';
            $section->description = 'Default section for questions';
            $section->sequence_number = 1;
            $section->save();
            
            $sections = $qcmPaper->sections()->orderBy('sequence_number')->get();
        }
        
        return view('inspector.qcm-questions.create', compact('qcmPaper', 'maxSequence', 'sections'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Debug info
        \Log::info('QCM Question Store Request Data:', $request->all());
        
        // Check if qcm_paper_id exists in the request
        if (!$request->has('qcm_paper_id')) {
            // If not in request, try to get it from the route parameter
            if ($request->route('qcmPaper')) {
                $request->merge(['qcm_paper_id' => $request->route('qcmPaper')->id]);
                \Log::info('Added qcm_paper_id from route parameter: ' . $request->route('qcmPaper')->id);
            } else {
                \Log::error('QCM Paper ID not found in request or route parameters');
            }
        }
        
        // Validate the request
        $validator = Validator::make($request->all(), [
            'qcm_paper_id' => 'required|exists:qcm_papers,id',
            'question_text' => 'required|string|max:1000',
            'question_type' => 'required|in:multiple_choice,yes_no',
            'image' => 'nullable|image|max:2048',
            'explanation' => 'nullable|string|max:1000',
            'answers' => 'required|array|min:2',
            'answers.*.text' => 'required|string|max:500',
            'answers.*.is_correct' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            \Log::warning('Validation failed: ' . json_encode($validator->errors()->toArray()));
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Check if the paper belongs to the user's school
        $paper = QcmPaper::find($request->qcm_paper_id);
        if (!$this->checkSchoolAccess($paper)) {
            abort(403, 'Unauthorized action.');
        }
        
        // Ensure papers don't exceed 10 questions
        $currentQuestionCount = QcmQuestion::where('qcm_paper_id', $request->qcm_paper_id)->count();
        if ($currentQuestionCount >= 10) {
            return redirect()->back()
                ->withErrors(['error' => 'A QCM paper can have a maximum of 10 questions.'])
                ->withInput();
        }
        
        // Check if at least one answer is marked as correct
        $hasCorrectAnswer = false;
        foreach ($request->answers as $answer) {
            if (isset($answer['is_correct'])) {
                $hasCorrectAnswer = true;
                break;
            }
        }
        
        if (!$hasCorrectAnswer) {
            return redirect()->back()
                ->withErrors(['error' => 'At least one answer must be marked as correct.'])
                ->withInput();
        }
        
        // For yes/no questions, ensure only two answers are provided
        if ($request->question_type === 'yes_no' && count($request->answers) !== 2) {
            return redirect()->back()
                ->withErrors(['error' => 'Yes/No questions must have exactly 2 options.'])
                ->withInput();
        }
        
        // Process and store the data
        DB::beginTransaction();
        
        try {
            // Create the question
            $question = new QcmQuestion();
            $question->qcm_paper_id = $request->qcm_paper_id;
            $question->question_text = $request->question_text;
            $question->question_type = $request->question_type;
            $question->explanation = $request->explanation;
            $question->sequence_number = $currentQuestionCount + 1;
            
            // If the section_id field exists in the database schema, assign the question to a section
            if (Schema::hasColumn('qcm_questions', 'section_id')) {
                if ($request->has('section_id')) {
                    // Use the section provided in the request
                    $question->section_id = $request->section_id;
                } else {
                    // Get the first section or create a default one
                    $section = QcmSection::where('qcm_paper_id', $request->qcm_paper_id)->orderBy('sequence_number', 'asc')->first();
                    if (!$section) {
                        // Create a default section
                        $section = new QcmSection();
                        $section->qcm_paper_id = $request->qcm_paper_id;
                        $section->title = 'Default Section';
                        $section->description = 'Default section for questions';
                        $section->sequence_number = 1;
                        $section->save();
                        \Log::info('Created a default section for the paper');
                    }
                    
                    $question->section_id = $section->id;
                    \Log::info('Assigned question to section ID: ' . $section->id);
                }
            }
            
            // Handle image upload if provided
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('qcm_questions', 'public');
                $question->image_path = $imagePath;
            }
            
            $question->save();
            
            // Create the answers
            foreach ($request->answers as $answerData) {
                $answer = new QcmAnswer();
                $answer->qcm_question_id = $question->id;
                
                // Check if the answer text is in 'text' or 'answer_text' field
                if (isset($answerData['text'])) {
                    $answer->answer_text = $answerData['text'];
                } elseif (isset($answerData['answer_text'])) {
                    $answer->answer_text = $answerData['answer_text'];
                } else {
                    throw new \Exception('Answer text not found in submitted data');
                }
                
                $answer->is_correct = isset($answerData['is_correct']) ? true : false;
                // Only set answer status if the column exists
                if (Schema::hasColumn('qcm_answers', 'status')) {
                    $answer->status = true;
                }
                $answer->save();
            }
            
            DB::commit();
            
            return redirect()->route('inspector.qcm-papers.questions.index', $request->qcm_paper_id)
                ->with('success', 'Question created successfully. ' . 
                       ($currentQuestionCount + 1 == 10 ? 'You have now added all 10 required questions.' : 
                        'You need to add ' . (10 - ($currentQuestionCount + 1)) . ' more questions.'));
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->withErrors(['error' => 'Failed to create question: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(QcmPaper $qcmPaper, QcmQuestion $question)
    {
        $this->authorize('view', $qcmPaper);
        
        // Ensure the question belongs to the paper
        if ($question->qcm_paper_id != $qcmPaper->id) {
            abort(404);
        }
        
        // Redirect to the questions index instead
        return redirect()->route('inspector.qcm-papers.questions.index', $qcmPaper);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(QcmPaper $qcmPaper, QcmQuestion $question)
    {
        $this->authorize('update', $qcmPaper);
        
        // Ensure the question belongs to the paper
        if ($question->qcm_paper_id != $qcmPaper->id) {
            abort(404);
        }
        
        $answers = $question->answers;
        
        // Get sections for the dropdown
        $sections = $qcmPaper->sections()->orderBy('sequence_number')->get();
        
        return view('inspector.qcm-questions.edit', compact('qcmPaper', 'question', 'answers', 'sections'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, QcmPaper $qcmPaper, QcmQuestion $question)
    {
        $this->authorize('update', $qcmPaper);
        
        // Ensure the question belongs to the paper
        if ($question->qcm_paper_id != $qcmPaper->id) {
            abort(404);
        }
        
        $validator = Validator::make($request->all(), [
            'question_text' => 'required|string',
            'question_type' => 'required|in:multiple_choice,yes_no',
            'image' => 'nullable|image|max:2048',
            'explanation' => 'nullable|string',
            'sequence_number' => 'required|integer|min:1',
            'answers' => 'required|array|min:2',
            'answers.*.id' => 'nullable|exists:qcm_answers,id',
            'answers.*.text' => 'required|string',
            'answers.*.is_correct' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Validate that exactly one answer is marked as correct
        $correctAnswers = collect($request->answers)->filter(function ($answer) {
            return isset($answer['is_correct']) && $answer['is_correct'] == 1;
        });
        
        if ($correctAnswers->count() != 1) {
            return redirect()->back()
                ->withErrors(['answers' => 'Exactly one answer must be marked as correct.'])
                ->withInput();
        }
        
        // Handle image upload
        $imagePath = $question->image_path;
        if ($request->hasFile('image')) {
            // Delete old image if it exists
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            
            $imagePath = $request->file('image')->store('qcm-questions', 'public');
        } elseif ($request->has('remove_image') && $request->remove_image) {
            // Delete old image if it exists
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            
            $imagePath = null;
        }
        
        DB::beginTransaction();
        
        try {
            // Update the question
            $question->question_text = $request->question_text;
            $question->question_type = $request->question_type;
            $question->image_path = $imagePath;
            $question->explanation = $request->explanation;
            $question->sequence_number = $request->sequence_number;
            $question->save();
            
            // Get existing answer IDs
            $existingAnswerIds = $question->answers->pluck('id')->toArray();
            $updatedAnswerIds = [];
            
            // Update or create answers
            foreach ($request->answers as $answerData) {
                if (isset($answerData['id']) && in_array($answerData['id'], $existingAnswerIds)) {
                    // Update existing answer
                    $answer = QcmAnswer::find($answerData['id']);
                    $answer->answer_text = $answerData['text'];
                    $answer->is_correct = isset($answerData['is_correct']) ? true : false;
                    
                    // Only update status if the column exists
                    if (Schema::hasColumn('qcm_answers', 'status')) {
                        $answer->status = true;
                    }
                    
                    $answer->save();
                    
                    $updatedAnswerIds[] = $answer->id;
                } else {
                    // Create new answer
                    $answer = new QcmAnswer();
                    $answer->qcm_question_id = $question->id;
                    $answer->answer_text = $answerData['text'];
                    $answer->is_correct = isset($answerData['is_correct']) ? true : false;
                    
                    // Only set status if the column exists
                    if (Schema::hasColumn('qcm_answers', 'status')) {
                        $answer->status = true;
                    }
                    
                    $answer->save();
                    
                    $updatedAnswerIds[] = $answer->id;
                }
            }
            
            // Delete answers that were not updated
            $answersToDelete = array_diff($existingAnswerIds, $updatedAnswerIds);
            if (!empty($answersToDelete)) {
                QcmAnswer::whereIn('id', $answersToDelete)->delete();
            }
            
            DB::commit();
            
            return redirect()->route('inspector.qcm-papers.questions.index', $qcmPaper)
                ->with('success', 'Question updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->withErrors(['error' => 'Failed to update question: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(QcmPaper $qcmPaper, QcmQuestion $question)
    {
        $this->authorize('update', $qcmPaper);
        
        // Ensure the question belongs to the paper
        if ($question->qcm_paper_id != $qcmPaper->id) {
            abort(404);
        }
        
        // Check if the question is used in any exams
        if ($question->examAnswers()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete this question as it is being used in exams.');
        }
        
        // Delete the image if it exists
        if ($question->image_path && Storage::disk('public')->exists($question->image_path)) {
            Storage::disk('public')->delete($question->image_path);
        }
        
        // Delete the answers
        $question->answers()->delete();
        
        // Delete the question
        $question->delete();
        
        return redirect()->route('inspector.qcm-papers.questions.index', $qcmPaper)
            ->with('success', 'Question deleted successfully.');
    }
    
    /**
     * Show the form for importing questions.
     */
    public function showImportForm(QcmPaper $qcmPaper)
    {
        $this->authorize('update', $qcmPaper);
        
        return view('inspector.qcm-questions.import', compact('qcmPaper'));
    }
    
    /**
     * Import questions from a CSV file.
     */
    public function import(Request $request, QcmPaper $qcmPaper)
    {
        $this->authorize('update', $qcmPaper);
        
        $validator = Validator::make($request->all(), [
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        try {
            // Get the file path and read the file
            $filePath = $request->file('csv_file')->getPathname();
            
            // Alternative CSV parsing using built-in PHP functions
            $file = fopen($filePath, 'r');
            
            // Read the header row
            $headers = fgetcsv($file);
            
            // Verify required columns exist
            $requiredColumns = ['question_text', 'answer_1', 'answer_2', 'correct_answer'];
            foreach ($requiredColumns as $column) {
                if (!in_array($column, $headers)) {
                    return redirect()->back()
                        ->withErrors(['csv_file' => 'The CSV file is missing required column: ' . $column])
                        ->withInput();
                }
            }
            
            $importedCount = 0;
            $errors = [];
            
            // Get the highest sequence number
            $maxSequence = $qcmPaper->questions()->max('sequence_number') ?? 0;
            
            DB::beginTransaction();
            
            // Process each row
            $rowNumber = 1; // Start at 1 because header is row 0
            
            while (($record = fgetcsv($file)) !== false) {
                $rowNumber++; // Increment for each row
                
                // Create an associative array from column names
                $row = [];
                foreach ($headers as $i => $header) {
                    $row[$header] = $record[$i] ?? '';
                }
                
                // Validate required fields
                if (empty($row['question_text']) || empty($row['answer_1']) || empty($row['answer_2']) || empty($row['correct_answer'])) {
                    $errors[] = "Row " . $rowNumber . ": Missing required fields.";
                    continue;
                }
                
                // Validate question type
                $questionType = $row['question_type'] ?? 'multiple_choice';
                if (!in_array($questionType, ['multiple_choice', 'yes_no'])) {
                    $questionType = 'multiple_choice';
                }
                
                // Validate correct answer
                $correctAnswer = (int) $row['correct_answer'];
                if ($correctAnswer < 1 || $correctAnswer > 4) {
                    $errors[] = "Row " . $rowNumber . ": Invalid correct answer. Must be between 1 and 4.";
                    continue;
                }
                
                try {
                    // Create the question
                    $question = new QcmQuestion();
                    $question->qcm_paper_id = $qcmPaper->id;
                    $question->question_text = $row['question_text'];
                    $question->question_type = $questionType;
                    $question->sequence_number = ++$maxSequence;
                    
                    // If section_id exists in schema, assign a default section
                    if (Schema::hasColumn('qcm_questions', 'section_id')) {
                        // Get the first section or create a default one
                        $section = QcmSection::where('qcm_paper_id', $qcmPaper->id)->orderBy('sequence_number', 'asc')->first();
                        if (!$section) {
                            // Create a default section
                            $section = new QcmSection();
                            $section->qcm_paper_id = $qcmPaper->id;
                            $section->title = 'Default Section';
                            $section->description = 'Default section for questions';
                            $section->sequence_number = 1;
                            $section->save();
                        }
                        
                        $question->section_id = $section->id;
                    }
                    
                    $question->save();
                    
                    // Create the answers
                    for ($i = 1; $i <= 4; $i++) {
                        $answerKey = 'answer_' . $i;
                        
                        // Skip empty answers (except for the first two which are required)
                        if ($i > 2 && empty($row[$answerKey])) {
                            continue;
                        }
                        
                        $answer = new QcmAnswer();
                        $answer->qcm_question_id = $question->id;
                        $answer->answer_text = $row[$answerKey];
                        $answer->is_correct = ($i == $correctAnswer);
                        // Only set answer status if the column exists
                        if (Schema::hasColumn('qcm_answers', 'status')) {
                            $answer->status = true;
                        }
                        $answer->save();
                    }
                    
                    $importedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Row " . $rowNumber . ": " . $e->getMessage();
                }
            }
            
            fclose($file);
            
            if (count($errors) > 0) {
                DB::rollBack();
                
                return redirect()->back()
                    ->withErrors(['csv_file' => $errors])
                    ->withInput();
            }
            
            DB::commit();
            
            return redirect()->route('inspector.qcm-papers.questions.index', $qcmPaper)
                ->with('success', $importedCount . ' questions imported successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('CSV import error: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['csv_file' => 'Failed to import questions: ' . $e->getMessage()])
                ->withInput();
        }
    }
    
    /**
     * Download a template CSV file for importing questions.
     */
    public function downloadTemplate()
    {
        try {
            // Define CSV contents directly as a string
            $csvContent = "question_text,question_type,answer_1,answer_2,answer_3,answer_4,correct_answer\n";
            $csvContent .= "What is the maximum speed limit on highways?,multiple_choice,90 km/h,110 km/h,130 km/h,150 km/h,3\n";
            $csvContent .= "Is it allowed to use a mobile phone while driving?,yes_no,Yes,No,,,2\n";
            
            // Set response headers
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="qcm_questions_template.csv"',
                'Cache-Control' => 'no-store, no-cache',
                'Pragma' => 'no-cache',
            ];
            
            // Return CSV as download
            return response($csvContent, 200, $headers);
        } catch (\Exception $e) {
            \Log::error('CSV template download error: ' . $e->getMessage());
            return back()->with('error', 'Failed to download template: ' . $e->getMessage());
        }
    }

    /**
     * Activate all answers for this QCM paper
     */
    public function activateAllAnswers(QcmPaper $qcmPaper)
    {
        $this->authorize('update', $qcmPaper);
        
        \Log::info('Activating QCM Paper: ' . $qcmPaper->id);
        
        try {
            DB::beginTransaction();
            
            // Check if the status column exists in qcm_answers table
            $hasStatusColumn = Schema::hasColumn('qcm_answers', 'status');
            
            if ($hasStatusColumn) {
                // Get all questions for this paper
                $questions = $qcmPaper->questions;
                
                // Update all answers to active status
                foreach ($questions as $question) {
                    $question->answers()->update(['status' => true]);
                    \Log::info('Activated answers for question ID: ' . $question->id);
                }
                
                DB::commit();
                
                return redirect()->route('inspector.qcm-papers.questions.index', $qcmPaper)
                    ->with('success', 'All questions have been activated successfully.');
            } else {
                // If the status column doesn't exist, just return a message
                DB::commit();
                
                // Activate the QCM paper itself if needed
                if (!$qcmPaper->status) {
                    $qcmPaper->status = true;
                    $qcmPaper->save();
                }
                
                return redirect()->route('inspector.qcm-papers.questions.index', $qcmPaper)
                    ->with('success', 'This system is configured to manage status at the paper level only. The QCM paper has been activated.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to activate answers: ' . $e->getMessage());
            
            return redirect()->route('inspector.qcm-papers.questions.index', $qcmPaper)
                ->with('error', 'Failed to activate answers: ' . $e->getMessage());
        }
    }
}

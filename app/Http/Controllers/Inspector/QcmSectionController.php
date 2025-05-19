<?php

namespace App\Http\Controllers\Inspector;

use App\Http\Controllers\Controller;
use App\Models\QcmPaper;
use App\Models\QcmSection;
use App\Models\QcmQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QcmSectionController extends Controller
{
    /**
     * Show the form for creating a new section.
     */
    public function create(QcmPaper $qcmPaper)
    {
        $this->authorize('update', $qcmPaper);
        
        return view('inspector.qcm-sections.create', compact('qcmPaper'));
    }
    
    /**
     * Store a newly created section in storage.
     */
    public function store(Request $request, QcmPaper $qcmPaper)
    {
        $this->authorize('update', $qcmPaper);
        
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Get the highest sequence number
        $maxSequence = $qcmPaper->sections()->max('sequence_number') ?? 0;
        
        // Create the section
        $section = new QcmSection();
        $section->qcm_paper_id = $qcmPaper->id;
        $section->title = $request->title;
        $section->description = $request->description;
        $section->sequence_number = $maxSequence + 1;
        $section->save();
        
        return redirect()->route('inspector.qcm-papers.show', $qcmPaper)
            ->with('success', 'Section created successfully.');
    }
    
    /**
     * Show the form for editing the specified section.
     */
    public function edit(QcmPaper $qcmPaper, QcmSection $section)
    {
        $this->authorize('update', $qcmPaper);
        
        // Ensure the section belongs to the paper
        if ($section->qcm_paper_id != $qcmPaper->id) {
            abort(404);
        }
        
        return view('inspector.qcm-sections.edit', compact('qcmPaper', 'section'));
    }
    
    /**
     * Update the specified section in storage.
     */
    public function update(Request $request, QcmPaper $qcmPaper, QcmSection $section)
    {
        $this->authorize('update', $qcmPaper);
        
        // Ensure the section belongs to the paper
        if ($section->qcm_paper_id != $qcmPaper->id) {
            abort(404);
        }
        
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sequence_number' => 'required|integer|min:1',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Update the section
        $section->title = $request->title;
        $section->description = $request->description;
        $section->sequence_number = $request->sequence_number;
        $section->save();
        
        return redirect()->route('inspector.qcm-papers.show', $qcmPaper)
            ->with('success', 'Section updated successfully.');
    }
    
    /**
     * Remove the specified section from storage.
     */
    public function destroy(QcmPaper $qcmPaper, QcmSection $section)
    {
        $this->authorize('update', $qcmPaper);
        
        // Ensure the section belongs to the paper
        if ($section->qcm_paper_id != $qcmPaper->id) {
            abort(404);
        }
        
        // If there are questions in this section, don't allow deletion
        if ($section->questions()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete section because it contains questions. Please move or delete the questions first.');
        }
        
        // Delete the section
        $section->delete();
        
        return redirect()->route('inspector.qcm-papers.show', $qcmPaper)
            ->with('success', 'Section deleted successfully.');
    }
    
    /**
     * Reorder sections.
     */
    public function reorder(Request $request, QcmPaper $qcmPaper)
    {
        $this->authorize('update', $qcmPaper);
        
        $validator = Validator::make($request->all(), [
            'sections' => 'required|array',
            'sections.*' => 'required|integer|exists:qcm_sections,id',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Invalid data provided.'], 400);
        }
        
        // Update the sequence numbers
        foreach ($request->sections as $index => $sectionId) {
            $section = QcmSection::find($sectionId);
            
            // Ensure the section belongs to the paper
            if ($section->qcm_paper_id != $qcmPaper->id) {
                continue;
            }
            
            $section->sequence_number = $index + 1;
            $section->save();
        }
        
        return response()->json(['success' => true, 'message' => 'Sections reordered successfully.']);
    }
    
    /**
     * Move questions to a different section.
     */
    public function moveQuestions(Request $request, QcmPaper $qcmPaper, QcmSection $section)
    {
        $this->authorize('update', $qcmPaper);
        
        // Ensure the section belongs to the paper
        if ($section->qcm_paper_id != $qcmPaper->id) {
            abort(404);
        }
        
        $validator = Validator::make($request->all(), [
            'question_ids' => 'required|array',
            'question_ids.*' => 'required|integer|exists:qcm_questions,id',
            'target_section_id' => 'required|integer|exists:qcm_sections,id',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Ensure the target section belongs to the paper
        $targetSection = QcmSection::findOrFail($request->target_section_id);
        if ($targetSection->qcm_paper_id != $qcmPaper->id) {
            return redirect()->back()
                ->with('error', 'The target section does not belong to this paper.');
        }
        
        // Move the questions
        QcmQuestion::whereIn('id', $request->question_ids)
            ->where('qcm_paper_id', $qcmPaper->id)
            ->update(['section_id' => $targetSection->id]);
        
        return redirect()->route('inspector.qcm-papers.questions.index', $qcmPaper)
            ->with('success', 'Questions moved successfully.');
    }
} 
# QCM (Interrogation Écrite) Implementation Plan

## Overview

The QCM (Questionnaire à Choix Multiple) module is a critical component of the driving exam platform, specifically for the "Interrogation Écrite" section. This document outlines the implementation strategy for creating a comprehensive QCM system that allows inspectors to create question sets, candidates to take exams, and administrators to track results.

## Requirements Analysis

### Core Requirements

1. **Question Bank Structure**:
   - 20 total QCM papers (sets)
   - Each paper contains exactly 10 questions
   - Two question types: multiple choice and yes/no (true/false)
   - Each question has a correct answer and potentially multiple incorrect answers

2. **Inspector Features**:
   - Create and manage QCM papers
   - Add questions manually or via spreadsheet import
   - Associate papers with specific permit categories
   - Review and edit questions

3. **Candidate Features**:
   - Access QCM exams after completing required courses
   - Take randomly selected paper (1 of 20 papers)
   - Complete 10 questions within 6-minute time limit
   - View results immediately after completion
   - Retake exams if necessary

4. **Admin Features**:
   - Track candidate QCM history and performance
   - View statistics and reports
   - Monitor pass/fail rates

5. **Grading System**:
   - 9-10 correct: 3 points
   - 7-8 correct: 2 points
   - 6 correct: 1 point
   - 5 correct: 0 points
   - <5 correct: Eliminatory (fails)

## Database Design

### Tables Structure

1. **`qcm_papers` Table**:
   ```
   id (PK)
   title
   description (nullable)
   permit_category_id (FK)
   created_by (FK to users)
   status (active/inactive)
   created_at
   updated_at
   ```

2. **`qcm_questions` Table**:
   ```
   id (PK)
   qcm_paper_id (FK)
   question_text
   question_type (multiple_choice/yes_no)
   image_path (nullable - for questions with images)
   sequence_number (1-10)
   created_at
   updated_at
   ```

3. **`qcm_answers` Table**:
   ```
   id (PK)
   qcm_question_id (FK)
   answer_text
   is_correct
   created_at
   updated_at
   ```

4. **`qcm_exams` Table**:
   ```
   id (PK)
   user_id (FK to candidates)
   qcm_paper_id (FK)
   started_at
   completed_at (nullable)
   duration_seconds
   correct_answers_count
   total_questions (always 10)
   points_earned (0-3)
   is_eliminatory
   status (in_progress/completed/timed_out)
   created_at
   updated_at
   ```

5. **`qcm_exam_answers` Table**:
   ```
   id (PK)
   qcm_exam_id (FK)
   qcm_question_id (FK)
   qcm_answer_id (FK, nullable)
   is_correct
   created_at
   updated_at
   ```

## User Interfaces

### Inspector Interfaces

1. **QCM Papers Management**:
   - List view of all papers with filtering by permit category
   - Create/Edit/Delete paper functionality
   - Status toggle (active/inactive)

2. **QCM Paper Detail**:
   - List of questions in the paper
   - Add/Edit/Delete questions
   - Reorder questions functionality

3. **Question Creation**:
   - Form with question text input
   - Question type selection (multiple choice/yes-no)
   - Image upload option
   - Answer options management with correct answer marking

4. **Bulk Import**:
   - Spreadsheet template download
   - File upload interface
   - Validation and error reporting
   - Preview before final import

### Candidate Interfaces

1. **QCM Exam Access**:
   - List of available exams based on completed courses
   - Exam instructions and rules
   - "Start Exam" button

2. **Exam Taking Interface**:
   - Timer display (6 minutes countdown)
   - Question display with answer options
   - Navigation between questions
   - Submit button
   - Auto-submission when timer expires

3. **Results Screen**:
   - Score display (X/10 correct)
   - Points earned (0-3)
   - Pass/Fail status
   - Option to review incorrect answers
   - Option to retake exam (if available)

### Admin Interfaces

1. **QCM Statistics Dashboard**:
   - Overview of pass/fail rates
   - Average scores by permit category
   - Trending difficult questions

2. **Candidate QCM History**:
   - Filterable list of all QCM attempts
   - Detailed view of individual attempts
   - Export functionality

## Implementation Strategy

### Phase 1: Database & Models Setup

1. Create migrations for all required tables
2. Implement Eloquent models with relationships:
   - QcmPaper hasMany QcmQuestions
   - QcmQuestion hasMany QcmAnswers
   - QcmQuestion belongsTo QcmPaper
   - QcmAnswer belongsTo QcmQuestion
   - User hasMany QcmExams
   - QcmExam belongsTo User, QcmPaper
   - QcmExam hasMany QcmExamAnswers
   - QcmExamAnswer belongsTo QcmExam, QcmQuestion, QcmAnswer

### Phase 2: Inspector QCM Management

1. Create CRUD controllers for QCM papers and questions
2. Implement paper management views
3. Develop question creation and editing interfaces
4. Add spreadsheet import functionality using Laravel Excel package
5. Implement validation and error handling

### Phase 3: Candidate QCM Experience

1. Create exam access control based on course completion
2. Develop exam session management (start, track, complete)
3. Implement the timed exam interface with countdown
4. Create the grading system based on specified rules
5. Design and implement the results display

### Phase 4: Admin Reporting

1. Create QCM statistics collection and processing
2. Implement admin dashboard views for QCM data
3. Develop detailed reporting features
4. Add export functionality

## Technical Considerations

### Exam Timer Implementation

The 6-minute timer will be implemented using:
- Server-side tracking of start time
- Client-side countdown with JavaScript
- Periodic AJAX updates to sync with server
- Auto-submission when timer expires

### Random Paper Selection

When a candidate starts an exam:
1. System will query all active papers for the relevant permit category
2. Randomly select one paper that the candidate hasn't taken recently
3. Create a new QcmExam record with the selected paper

### Spreadsheet Import

For the spreadsheet import feature:
1. Create a standardized Excel template with validation rules
2. Use Laravel Excel package for processing uploads
3. Implement row-by-row validation with error collection
4. Provide preview and confirmation before final import

### Security Considerations

1. Prevent access to exams until prerequisites are met
2. Implement anti-cheating measures:
   - Question order randomization
   - Answer option randomization
   - Session monitoring
3. Secure answer validation on server-side only

## Integration with Existing System

### Course Completion Integration

- Link QCM access to course completion status
- Add QCM-specific courses for exam preparation
- Track which permit categories require QCM completion

### User Dashboard Updates

- Add QCM exam history to candidate dashboard
- Include QCM statistics in inspector and admin dashboards
- Implement notifications for QCM results

## Testing Strategy

1. **Unit Tests**:
   - Test grading logic
   - Test random paper selection
   - Test timer functionality

2. **Feature Tests**:
   - Test complete exam workflow
   - Test spreadsheet import
   - Test reporting accuracy

3. **User Acceptance Testing**:
   - Verify timer accuracy
   - Test on multiple devices
   - Validate grading system accuracy

## Timeline

1. **Week 1**: Database setup and model implementation
2. **Week 2**: Inspector interfaces for QCM management
3. **Week 3**: Candidate exam interface and timer implementation
4. **Week 4**: Admin reporting and statistics
5. **Week 5**: Testing, refinement, and deployment

## Conclusion

The QCM module will be a comprehensive system that accurately replicates the "Interrogation Écrite" portion of the driving exam. By implementing the features described in this document, we will create a robust system that allows inspectors to create and manage question banks, candidates to take timed exams with immediate feedback, and administrators to track performance and statistics.

This implementation will integrate seamlessly with the existing course system and user roles, maintaining the platform's cohesive user experience while adding this critical examination functionality. 